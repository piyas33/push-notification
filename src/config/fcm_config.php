<?php

return [
    'fcm_api_url' => "https://fcm.googleapis.com/v1/projects/". env('FCM_PROJECT_ID') . "/messages:send",
    'fcm_json_path' => base_path() . '/' . env('FCM_JSON_PATH'),
    'fcm_enable_queue' => env('FCM_ENABLE_QUEUE', false),
];
