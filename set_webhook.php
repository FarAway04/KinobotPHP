<?php

// API tokenni environmentdan oladi
$API_TOKEN = getenv('API_TOKEN');

// Shu yerga o'z manzilingni qo'y:
$webhook_url = 'https://kinobotphp.onrender.com/Bot.php';

// Telegram API URL
$api_url = "https://api.telegram.org/bot$API_TOKEN/setWebhook?url=" . $webhook_url;

// So'rov yuborish
$response = file_get_contents($api_url);

// Javobni chiqarish
echo $response;

?>
