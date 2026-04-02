<?php

namespace App\Helpers;

use CodeIgniter\Config\Services;

class FirebaseHelper
{
    /**
     * Sends a push notification to a specific FCM token
     *
     * @param string $token The FCM token of the recipient device
     * @param string $title Notification title
     * @param string $body Notification body content
     * @param array $data Additional data payload
     * @return bool|string True if successful, false or error message on failure
     */
    public static function sendNotification($token, $title, $body, $data = [])
    {
        if (empty($token)) {
            return false;
        }

        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            log_message('error', 'FCM: Failed to get access token');
            return false;
        }

        // Project ID from service account
        $serviceAccountPath = APPPATH . 'Config/sicape-seruyan-firebase-adminsdk-fbsvc-72a6469f42.json';
        if (!file_exists($serviceAccountPath)) {
            log_message('error', 'FCM: Service account file not found');
            return false;
        }
        $key = json_decode(file_get_contents($serviceAccountPath), true);
        $projectId = $key['project_id'];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        // Format message for v1 API
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data), // v1 requires data values to be strings
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'priority' => 'high'
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);
        
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            log_message('error', 'FCM Send Error: ' . $error);
            return false;
        }

        curl_close($ch);
        log_message('debug', 'FCM Response: ' . $result);
        
        return true;
    }

    private static function getAccessToken()
    {
        $serviceAccountPath = APPPATH . 'Config/sicape-seruyan-firebase-adminsdk-fbsvc-72a6469f42.json';
        if (!file_exists($serviceAccountPath)) {
            return false;
        }

        $key = json_decode(file_get_contents($serviceAccountPath), true);
        $jwt = self::generateJwt($key);

        $url = 'https://oauth2.googleapis.com/token';
        $postData = 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        return $data['access_token'] ?? false;
    }

    private static function generateJwt($key)
    {
        $now = time();
        $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signatureInput = $header . "." . $payload;
        openssl_sign($signatureInput, $signature, $key['private_key'], 'SHA256');
        $signature = base64url_encode($signature);

        return $signatureInput . "." . $signature;
    }
}

function base64url_encode($data)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}
