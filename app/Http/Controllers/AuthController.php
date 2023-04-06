<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        $validator = Validator::make( $request->all(), [
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:6']
        ] );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::query()->create([
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json($user);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            return response()->json(['message' => 'Credentials not valid'], 401);
        }

        $user = User::find(Auth::id());

        try {
            //$tokens = AuthUtils::createTokens($request->email, $request->password);
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        } catch (\Exception $e) {
            return response()->json(['message' => "Login server error", 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            //'token_type' => $tokens['token_type'],
            //'access_token' => $tokens['access_token'],
            'access_token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json('Successfully logged out');
    }
}
