<?php
// 1️⃣ TOKEN va ADMIN_ID ni Render environment dan oladi:
$token = getenv("API_TOKEN");
$admin_id = getenv("ADMIN_ID");

// 2️⃣ Telegram API chaqiruvchi qulay funksiya
function send($method, $data) {
    global $token;
    $url = "https://api.telegram.org/bot$token/$method";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// 3️⃣ Kiruvchi update ni ol
$update = json_decode(file_get_contents("php://input"), true);

$message = $update["message"] ?? null;
$callback = $update["callback_query"] ?? null;

if ($message) {
    $chat_id = $message["chat"]["id"];
    $text = $message["text"] ?? "";

    // Majburiy kanallar
    $channels = ["@YOUR_CHANNEL_1", "@YOUR_CHANNEL_2"];

    function isSubscribed($chat_id, $channels) {
        global $token;
        foreach ($channels as $channel) {
            $url = "https://api.telegram.org/bot$token/getChatMember?chat_id=$channel&user_id=$chat_id";
            $result = json_decode(file_get_contents($url), true);
            $status = $result['result']['status'] ?? 'left';
            if ($status == 'left') return false;
        }
        return true;
    }

    if ($text == "/start") {
        if ($chat_id == $admin_id) {
            $keyboard = [
                'keyboard' => [
                    [['text' => '📽 Kinolar'], ['text' => '📢 Xabarlar']],
                    [['text' => '👨‍💻 Adminlar'], ['text' => '⭐ Super Userlar']],
                    [['text' => '📡 Kanallar'], ['text' => '📊 Statistika']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "👋 Admin panelga xush kelibsiz!",
                'reply_markup' => json_encode($keyboard)
            ]);
        } else {
            if (isSubscribed($chat_id, $channels)) {
                send("sendMessage", [
                    'chat_id' => $chat_id,
                    'text' => "✅ Kanallarga obuna bo‘ldingiz!\nIltimos kino kodini yuboring."
                ]);
            } else {
                $buttons = [];
                foreach ($channels as $ch) {
                    $buttons[] = [['text' => $ch, 'url' => "https://t.me/" . ltrim($ch, "@")]];
                }
                $buttons[] = [['text' => "✅ Tekshirish", 'callback_data' => "check_sub"]];
                send("sendMessage", [
                    'chat_id' => $chat_id,
                    'text' => "👋 Botdan foydalanish uchun quyidagi kanallarga obuna bo‘ling:",
                    'reply_markup' => json_encode(['inline_keyboard' => $buttons])
                ]);
            }
        }
    }

    if ($chat_id == $admin_id) {
        // KINOLAR tugmasi
        if ($text == "📽 Kinolar") {
            $keyboard = [
                'keyboard' => [
                    [['text' => '🎞 Kino qo‘shish'], ['text' => '🗑 Kino o‘chirish']],
                    [['text' => '✏ Kino tahrirlash'], ['text' => '◀ Ortga']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📽 Kinolar menyusi:",
                'reply_markup' => json_encode($keyboard)
            ]);
        }
        // ORTGA tugmasi
        if ($text == "◀ Ortga") {
            $keyboard = [
                'keyboard' => [
                    [['text' => '📽 Kinolar'], ['text' => '📢 Xabarlar']],
                    [['text' => '👨‍💻 Adminlar'], ['text' => '⭐ Super Userlar']],
                    [['text' => '📡 Kanallar'], ['text' => '📊 Statistika']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "🔙 Asosiy menyuga qaytdingiz!",
                'reply_markup' => json_encode($keyboard)
            ]);
        }
        // Xabarlar tugmasi
        if ($text == "📢 Xabarlar") {
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📢 Xabarlar menyusi hozircha tayyor emas!"
            ]);
        }
        // Statistika tugmasi
        if ($text == "📊 Statistika") {
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📊 Statistika: \nKinolar: ... \nFoydalanuvchilar: ... \nAdminlar: ... \nSuper userlar: ... \nKanallar: ..."
            ]);
        }
    }
}

if ($callback) {
    $cid = $callback['message']['chat']['id'];
    $mid = $callback['message']['message_id'];
    $data = $callback['data'];

    if ($data == "check_sub") {
        if (isSubscribed($cid, ["@YOUR_CHANNEL_1", "@YOUR_CHANNEL_2"])) {
            send("editMessageText", [
                'chat_id' => $cid,
                'message_id' => $mid,
                'text' => "✅ Obuna tekshirildi!\nEndi kino kodini yuboring!"
            ]);
        } else {
            send("answerCallbackQuery", [
                'callback_query_id' => $callback['id'],
                'text' => "❌ Hali barcha kanallarga obuna bo‘lmadingiz!",
                'show_alert' => true
            ]);
        }
    }
}
