<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtLibrary
{
    private $key;

    public function __construct()
    {
        // Use a default key if not set in .env (for development simplicity, but recommend .env)
        $this->key = getenv('JWT_SECRET') ?: 'sicape_secret_key_2024_secure';
    }

    public function generateToken($user)
    {
        $iat = time();
        $exp = $iat + (60 * 60 * 24 * 30); // 30 days expiration for mobile app

        $payload = [
            'iss' => 'sicape_api',
            'aud' => 'sicape_mobile',
            'sub' => $user['id'],
            'iat' => $iat,
            'exp' => $exp,
            'data' => [
                'id' => $user['id'],
                'nip' => $user['nip'],
                'role' => $user['role'],
            ]
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return false;
        }
    }
}
