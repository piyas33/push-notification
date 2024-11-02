# push-notification

#Install

```jsx
composer require piyas33/push-notification

php artisan vendor:publish --tag=config --force
```

`.env`

```jsx
FCM_PROJECT_ID="blog-12345"
FCM_JSON_PATH="/public/blog-12345-firebase-adminsdk-7hpyr-e78c047e12.json"
FCM_ENABLE_QUEUE= false
```
if `FCM_ENABLE_QUEUE` is true :

```jsx
QUEUE_CONNECTION=database
```
