<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Lista;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ListaController extends Controller
{
    public function index(Request $request) : JsonResponse {

        $validator = Validator::make($request->all(), [
            "shortcode" => ['sometimes', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();

        $data=$request -> only('shortcode');
        if (isset($data['shortcode'])) {
            $lists = Lista::query()->where('shortcode', $request->shortcode);
        } else {
            $lists = Lista::query()->where('user_id', $user->id);
        }

        $lists = $lists->get();

        return response()->json($lists);

    }

    public function create(Request $request) : JsonResponse {
        $validator = Validator::make($request->all(), [
            "list_name" => ['required', 'string'],
            "tipologia" => ['sometimes', 'in:'.implode(',',Lista::TIPOLOGIA)]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = $request->user();
        $data = $request->only('tipologia');

        $shortcode = null;

        if ((isset($data['tipologia']))) {
            if ($data['tipologia'] === Lista::PUBBLICA) {
                $shortcode = self::generateShortcode();
            }
        }
        
        $list = Lista::query()->create([
            'list_name' => $request->input('list_name'),
            'user_id' => $user->id,
            "shortcode" => $shortcode,
            "tipologia" => $request->input('tipologia')
        ]);

        return response()->json($list, 201);
    }

    public function share (Request $request, Lista $lista) : JsonResponse {
        $shortcode = Lista::query()->where('id', $lista->id) -> pluck('shortcode') -> toArray();
        $link = $request->getHttpHost().'/api/lists?shortcode='.$shortcode[0];
        return response()->json($link, 200);
    }

    public function updateKey (Request $request, Lista $lista) : JsonResponse {

        $user = $request->user();
        if ($lista->user_id !== $user->id) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        Lista::query()->where('id', $lista->id)->update([
            'shortcode' => self::generateShortcode()
        ]);

        return response()->json($lista->refresh());
    }

    public function updateTipologia (Request $request, Lista $lista) : JsonResponse {

        $user = $request->user();
        if ($lista->user_id !== $user->id) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        if($lista->tipologia === Lista::PRIVATA) {
            Lista::query()->where('id', $lista->id)->update([
                'shortcode' => self::generateShortcode(),
                'tipologia' => Lista::PUBBLICA
            ]);
        } else if ($lista->tipologia === Lista::PUBBLICA) {
            Lista::query()->where('id', $lista->id)->update([
                'shortcode' => null,
                'tipologia' => Lista::PRIVATA
            ]);
        }

        return response()->json($lista->refresh());
    }

    public function delete(Request $request, Lista $lista): JsonResponse
    {

        $user = $request->user();

        if($lista->user_id !== $user->id && $lista->tipologia === LISTA::PRIVATA) {
            return response()->json(['message' => 'Operation not permitted'], 401);
        }

        if($lista->tipologia === LISTA::PUBBLICA && $lista->user_id !== $user->id) {
            $validator = Validator::make($request->all(), [
                "shortcode" => ['required', 'string']
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            if ($lista->shortcode !== $request->shortcode) {
                return response()->json(['message' => 'Operation not permitted'], 401);
            }
        }

        $lista->delete();
        return response()->json(null, 204);
    }

    private function generateShortcode() : String {
        $shortcode = Str::random(8);
        return $shortcode;
    }

}
