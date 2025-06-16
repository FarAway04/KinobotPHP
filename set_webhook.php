<?php
// .env-dan API token
$API_TOKEN = getenv('API_TOKEN');

// Webhook URL — faqat shu yerga o'zgartir!
$WEBHOOK_URL = 'https://kinobotphp.onrender.com/Bot.php';

// To'liq Telegram API URL
$API_URL = "https://api.telegram.org/bot$API_TOKEN/setWebhook?url=" . urlencode($WEBHOOK_URL);

// Curl orqali so'rov yuborish
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Natija chiqarish
echo $response;
?>
