<?php

namespace FCM\PushNotification;

use Exception;
use Fcm\PushNotification\Jobs\PushNotificationJob;
use FCM\PushNotification\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class PushNotification
{

    public static function sendToOne(string $deviceToken, string $title, string $body, string $image = '', array $data = null): bool
    {
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => compact('title', 'body', 'image'),
                'data' => self::formatData($data),
            ],
        ];

        return self::send($payload);
    }

    public static function sendToTopic(string $topic, string $title, string $body, string $image = '', array $data = null): bool
    {
        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => compact('title', 'body', 'image'),
                'data' => self::formatData($data),
            ],
        ];

        return self::send($payload);
    }

    private static function send(array $data): bool
    {
        $url = config('fcm_config.fcm_api_url');
        $enableQueue = config('fcm_config.fcm_enable_queue');

        try {
            if($enableQueue) {
                $response = PushNotificationJob::dispatch($url, 'POST', $data);
            } else {
                $response = PushNotificationService::sendRequest($url, 'POST', $data);
            }
            Log::info('[Notification] SENT', ['data' => $data, 'response' => $response]);
            return true;
        } catch (Exception $e) {
            Log::error('[Notification] ERROR', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public static function getTopicsByToken(string $token): array
    {
        $url = "https://iid.googleapis.com/iid/info/{$token}?details=true";

        try {
            $response = PushNotificationService::sendRequest($url, 'GET');
            $decodedResponse = json_decode($response, true);
            $topics = $decodedResponse['rel']['topics'] ?? [];

            return array_map(
                function ($topic, $name) {
                    return [
                        'name' => $name,
                        'addDate' => $topic['addDate']
                    ];
                },
                $topics,
                array_keys($topics)
            );
        } catch (Exception $e) {
            Log::error('[ERROR] get topics by token', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public static function subscribeTopic(string $deviceToken, string $topic): bool
    {
        $enableQueue = config('fcm_config.fcm_enable_queue');
        $url = "https://iid.googleapis.com/iid/v1/{$deviceToken}/rel/topics/{$topic}";

        try {
            if($enableQueue) {
                $response = PushNotificationJob::dispatch($url, 'POST');
            } else {
                $response = PushNotificationService::sendRequest($url, 'POST');
            }
            Log::info('[SUCCESS] subscribed to topic', ['topic' => $topic, 'response' => $response]);
            return true;
        } catch (Exception $e) {
            Log::error('[ERROR] subscribe to topic', ['topic' => $topic, 'message' => $e->getMessage()]);
            return false;
        }
    }

    public static function unsubscribeTopic(array $tokens, string $topic): bool
    {

        if (count($tokens) > 999) {
            throw new Exception("Too many tokens, limit is 999. Received " . count($tokens));
        }

        $enableQueue = config('fcm_config.fcm_enable_queue');
        $url = "https://iid.googleapis.com/iid/v1:batchRemove";
        $body = [
            'to' => "/topics/{$topic}",
            'registration_tokens' => $tokens,
        ];

        try {

            if($enableQueue) {
                $response = PushNotificationJob::dispatch($url, 'POST', $body);
            } else {
                $response = PushNotificationService::sendRequest($url, 'POST', $body);
            }

            Log::info('[SUCCESS] unsubscribed from topic', ['topic' => $topic, 'response' => $response]);
            return true;
        } catch (Exception $e) {
            Log::error('[ERROR] unsubscribe from topic', ['topic' => $topic, 'message' => $e->getMessage()]);
            return false;
        }
    }

    public static function subscribeMultipleDevice(array $tokens, string $topic): bool
    {
        if (count($tokens) > 999) {
            throw new Exception("Too many tokens, limit is 999. Received " . count($tokens));
        }

        $enableQueue = config('fcm_config.fcm_enable_queue');
        $url = "https://iid.googleapis.com/iid/v1:batchAdd";
        $body = [
            'to' => "/topics/{$topic}",
            'registration_tokens' => $tokens,
        ];

        try {

            if($enableQueue) {
                $response = PushNotificationJob::dispatch($url, 'POST', $body);
            } else {
                $response = PushNotificationService::sendRequest($url, 'POST', $body);
            }

            Log::info('[SUCCESS] subscribed multiple devices to topic', ['topic' => $topic, 'response' => $response]);
            return true;
        } catch (Exception $e) {
            Log::error('[ERROR] subscribe multiple devices to topic', ['topic' => $topic, 'message' => $e->getMessage()]);
            return false;
        }
    }

    public static function subscribeMultipleTopic(string $deviceToken, array $topics): bool
    {
        if (count($topics) > 25) {
            throw new Exception("Too many topic, limit is 25. Received " . count($topics));
        }

        try {
            foreach ($topics as $topic) {
                self::subscribeTopic($deviceToken, $topic);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('[ERROR] subscribe to multiple topic', ['topics' => $topics, 'message' => $e->getMessage()]);
            return false;
        }
    }

    private static function formatData(?array $data): array
    {
        if (is_null($data)) {
            return [];
        }

        return array_map(function ($value) {
            return is_array($value) || is_object($value) || is_integer($value) ? json_encode($value) : $value;
        }, $data);
    }
}
