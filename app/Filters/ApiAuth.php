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
        $token = null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Also check for token in query parameter (useful for links/downloads)
        if (!$token) {
            $token = $request->getGet('token');
        }

        if (!$token) {
            $response = service('response');
            $response->setJSON(['status' => 401, 'error' => 'Token Required', 'messages' => ['error' => 'Authentication header missing or query token missing']]);
            $response->setStatusCode(401);
            return $response;
        }

        // If token was from header, ensure it's a Bearer token.
        // If token was from query param, this check is skipped as it's not a Bearer format requirement.
        // The original logic for "Invalid Token Format" was specifically for the Bearer header.
        // If $token is not null at this point, it means we either got it from Bearer header or query param.
        // The original "Invalid Token Format" error was for when $authHeader existed but no Bearer was found.
        // With the new logic, if $authHeader existed but no Bearer, $token would still be null,
        // and the previous `if (!$token)` block would have caught it.
        // Therefore, this specific error message "Token must be Bearer token" is now only relevant
        // if we explicitly want to enforce Bearer format for header tokens, and allow any format for query tokens.
        // Given the instruction, I'm placing the block as provided, which effectively makes it unreachable
        // if the previous `if (!$token)` block is hit, or if a token was found.
        // However, to maintain the spirit of the original error for header tokens,
        // a more robust check might be needed if the intent is to differentiate.
        // For now, I'll place it as instructed.
        if (!$token) { // This condition will not be met if a token was found in header or query.
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
