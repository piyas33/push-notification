# push-notification

#Install

```jsx
composer require piyas33/push-notification

php artisan vendor:publish --tag=config --force
```

`.env`

```jsx
FCM_PROJECT_ID="project_id"
FCM_JSON_PATH="your_fcm_json_path"
FCM_ENABLE_QUEUE= false
```
if `FCM_ENABLE_QUEUE` is true :

```jsx
QUEUE_CONNECTION=database
```
