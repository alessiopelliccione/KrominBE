<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:'.implode(',',Todo::STATUSES)],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'with_deleted' => ['sometimes'],
            'list' => ['sometimes']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();
        $data = $request->only('content', 'status', 'from', 'to', 'list');
        $todos = Todo::query()->where('user_id', $user->id);

        if (isset($data['content'])) {
            $todos = $todos->where('content', 'LIKE', "%{$data['content']}%");
        }
        if (isset($data['status'])) {
            $todos = $todos->where('status',  $data['status']);
        }
        if (isset($data['from'])) {
            $date_from = Carbon::createFromDate($data['from'])->toDateTimeString();
            $todos = $todos->where("created_at", ">=", $date_from);
        }
        if (isset($data['to'])) {
            $date_to = Carbon::createFromDate($data['to'])->toDateTimeString();
            $todos = $todos->where('created_at', "<=", $date_to);
        }
        if($request->with_deleted){
            $todos = $todos->withTrashed();
        }

        //Se la lista non viene settata la ricerca viene fatta per tutti i todo (in base agli altri filtri)
        if(isset($data['list'])) {
            $todos = $todos->where('list', $data['list']);
        }

        $todos = $todos->get();

        return response()->json($todos);
    }

    public function show(Request $request, Todo $todo): JsonResponse
    {
        $user = $request->user();

        if ($todo->user_id !== $user->id) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        return response()->json($todo);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string'],
            'position' => ['required', 'numeric'],
            "list" => ['required', 'exists:listas,id', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        /**
         * @todo check owner della lista 
         * */

        $user = $request->user();

        $lista = DB::table('listas')->join('users', 'users.id', '=', 'listas.user_id')->where('user_id', $user->id)->get();
        if($lista->first()===null) {
            return response()->json('Forbidden', 403);
        }

        $todo = Todo::query()->create([
            'content' => $request->input('content'),
            'position' => $request->input('position'),
            'list' => $request->input('list'),
            'user_id' => $user->id,
        ]);

        return response()->json($todo, 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => ['sometimes', 'string'],
            'position' => ['sometimes', 'numeric'],
            'list' => ['sometimes', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();
        if ($todo->user_id !== $user->id) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        $data = $request->only('content', 'position', 'list');
        $todo->update($data);

        return response()->json($todo->refresh());
    }

    public function delete(Request $request, Todo $todo): JsonResponse
    {
        $user = $request->user();
        if ($todo->user_id !== $user->id) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        $todo->delete();
        return response()->json(null, 204);
    }

    public function order(Request $request): JsonResponse
    {
        $user = $request->user();
        $todos = Todo::query()->where('user_id', $user->id)->get();
        $validator = Validator::make($request->all(), [
            'todos' => ['required', 'array'],
            'todos.*.id' => ['required', function ($attribute, $value, $fail) use ($user, $todos) {
                $todo = $todos->where('id', $value)->first();
                if (!isset($todo)) {
                    $fail('Todo not found');
                }
            }],
            'todos.*.position' => ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $todo_ids = array_column($request->todos, 'id');
        $todos_request = $todos->whereIn('id', $todo_ids);
        if ($todos->count() !== $todos_request->count()) {
            return response()->json(['message' => 'Set position on all todos'], 400);
        }

        $positions = array_column($request->todos, 'position');
        if (count($positions) !== count(array_unique($positions))) {
            return response()->json(['message' => 'Unable to set equal positions'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->todos as $todo) {
                Todo::query()->where('id', $todo['id'])->update(['position' => $todo['position']]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json('Positions updated');
    }

    public function report(Request $request) : JsonResponse {

        $validator = Validator::make($request->all(), [
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();
        $data = $request->only('start_date', 'end_date');

        if (isset($data['start_date']) && isset($data['end_date'])) {

            $todos_number = Todo::query()->where('user_id', $user->id)->count();
            $five_days_todos = Todo::query()->where('user_id', $user->id)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->where('start_date', '>', $data['start_date'])
                ->where('end_date', '<', $data['end_date'])
                ->whereRaw('DATEDIFF(end_date,start_date) < 5')
                ->count();
        } else {
            $todos_number = Todo::query()->where('user_id', $user->id)->count();
            $five_days_todos = Todo::query()->where('user_id', $user->id)
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereRaw('DATEDIFF(end_date,start_date) < 10')
                ->count();
        }
        
        $percentage = $five_days_todos*100/$todos_number;

        return response()->json($percentage, 200);
    }
}
