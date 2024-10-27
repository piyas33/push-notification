<?php

namespace Fcm\PushNotification\Jobs;

use Exception;
use FCM\PushNotification\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $method;
    protected $data;

    public function __construct(string $url, string $method, ?array $data = null)
    {
        $this->url = $url;
        $this->method = $method;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            $response = PushNotificationService::sendRequest($this->url, $this->method, $this->data);
            Log::info('[PushNotification] Queue Done', ['response' => $response]);
        } catch (Exception $e) {
            Log::error('[PushNotification] Failed to Queue', ['message' => $e->getMessage()]);
        }
    }
}
