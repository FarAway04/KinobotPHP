<?php
$token = getenv("API_TOKEN");
$url = "https://kinobotphp.onrender.com";

$api = "https://api.telegram.org/bot$token/setWebhook?url=" . urlencode($url);

$response = file_get_contents($api);

echo $response;
