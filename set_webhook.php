<?php
$token = getenv("API_TOKEN"); // TOKEN ni faqat environment’dan oladi!
$url = "https://kinobotphp.onrender.com/Bot.php";

$set = file_get_contents("https://api.telegram.org/bot$token/setWebhook?url=$url");
var_dump($set);
