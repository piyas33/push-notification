<?php

namespace FCM\PushNotification\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Cache;

class PushNotificationService
{

    private const FCM_TOKEN_CACHE_KEY = 'FCM_TOKEN_CACHE_KEY';
    private const FCM_TOKEN_CACHE_DURATION = 55 * 60; // 55 minutes

    public static function sendRequest(string $url, string $method, array $data = null): string
    {
        $ch = curl_init($url);

        $headers = self::getHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        return $response;
    }

    private static function getHeaders(): array
    {
        return [
            'Authorization: Bearer ' . self::getAccessToken(),
            'Content-Type: application/json',
            'access_token_auth: true',
        ];
    }

    private static function getAccessToken(): string
    {
        return Cache::remember(self::FCM_TOKEN_CACHE_KEY, self::FCM_TOKEN_CACHE_DURATION, function () {
            $client = new GoogleClient();
            $client->setAuthConfig(config('fcm_config.fcm_json_path'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $token = $client->fetchAccessTokenWithAssertion();
            return $token['access_token'];
        });
    }

}