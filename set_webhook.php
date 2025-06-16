<?php
$token = getenv("API_TOKEN");
$webhook_url = "https://kinobotphp.onrender.com/Bot.php";

$res = file_get_contents("https://api.telegram.org/bot$token/setWebhook?url=$webhook_url");

echo $res;
