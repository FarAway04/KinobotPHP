<?php
$token = getenv("API_TOKEN");
$url = "https://YOUR_PROJECT_URL/Bot.php";

$api = "https://api.telegram.org/bot$token/setWebhook?url=" . urlencode($url);

$response = file_get_contents($api);

echo $response;
