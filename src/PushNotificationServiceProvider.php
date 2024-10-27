<?php

namespace FCM\PushNotification;

use Illuminate\Support\ServiceProvider;

class PushNotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/fcm_config.php' => config_path('fcm_config.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/fcm_config.php', 'fcm_config'
        );
    }
}
