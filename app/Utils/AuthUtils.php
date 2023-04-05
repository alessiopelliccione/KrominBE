<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client as PassportClient;

class AuthUtils
{
    public static function createTokens(string $email, string $password): array
    {
        $passport = PassportClient::query()->where('password_client', true)->first();

        $tokens = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'passwd',
            'client_id' => $passport->id,
            'client_secret' => $passport->secret,
            'username' => $email,
            'password' => $password,
            'scope' => '*',
            'name' => 'Access Token'
        ])->json();

        if(!isset($tokens)){
            file_put_contents("php://stdout", "[WARNING] Cannot create access tokens" . PHP_EOL, FILE_APPEND);
            throw new \Exception("Cannot create access tokens", 500);
        }

        if(isset($tokens['error'])){
            file_put_contents("php://stdout", "[WARNING] Cannot create access tokens: {$tokens['error']}" . PHP_EOL, FILE_APPEND);
            throw new \Exception("Cannot create access tokens", 500);
        }

        return $tokens;
    }
}
