<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtLibrary;
use CodeIgniter\API\ResponseTrait;

class ApiAuth implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader) {
            $response = service('response');
            $response->setJSON(['status' => 401, 'error' => 'Token Required', 'messages' => ['error' => 'Authentication header missing']]);
            $response->setStatusCode(401);
            return $response;
        }

        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            $response = service('response');
            $response->setJSON(['status' => 401, 'error' => 'Invalid Token Format', 'messages' => ['error' => 'Token must be Bearer token']]);
            $response->setStatusCode(401);
            return $response;
        }

        $jwtLib = new JwtLibrary();
        $userData = $jwtLib->validateToken($token);

        if (!$userData) {
            $response = service('response');
            $response->setJSON(['status' => 401, 'error' => 'Invalid Token', 'messages' => ['error' => 'Token is invalid or expired']]);
            $response->setStatusCode(401);
            return $response;
        }

        // Attach user data to request for controller usage
        $request->userData = $userData;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
