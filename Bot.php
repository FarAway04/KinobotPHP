<?php
// 📌 TOKEN va ADMIN_ID Render environment'dan oladi
$token = getenv("API_TOKEN");
$admin_id = getenv("ADMIN_ID");

// 📌 SQLite ulash
$db = new PDO("sqlite:kinolar.db");

// 📌 Bazani tayyorlash (agar jadval bo‘lmasa)
$db->exec("CREATE TABLE IF NOT EXISTS kinolar (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, link TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS adminlar (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER UNIQUE)");
$db->exec("CREATE TABLE IF NOT EXISTS kanallar (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE)");

// 📌 Telegram API chaqiruvchi
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

// 📌 Foydalanuvchi xabari
$update = json_decode(file_get_contents("php://input"), true);
$message = $update["message"] ?? null;
$callback = $update["callback_query"] ?? null;

if ($message) {
    $chat_id = $message["chat"]["id"];
    $text = $message["text"] ?? "";

    // 🔑 Majburiy kanallar
    $channels = $db->query("SELECT username FROM kanallar")->fetchAll(PDO::FETCH_COLUMN);

    // 📌 Obuna tekshirish
    function isSubscribed($chat_id, $channels) {
        global $token;
        foreach ($channels as $channel) {
            $url = "https://api.telegram.org/bot$token/getChatMember?chat_id=$channel&user_id=$chat_id";
            $res = json_decode(file_get_contents($url), true);
            $status = $res["result"]["status"] ?? "left";
            if ($status == "left") return false;
        }
        return true;
    }

    // 🔹 /start
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
                    'text' => "✅ Kanallarga obuna bo‘ldingiz!\nKino kodini yuboring:"
                ]);
            } else {
                $btns = [];
                foreach ($channels as $ch) {
                    $btns[] = [['text' => $ch, 'url' => "https://t.me/" . ltrim($ch, "@")]];
                }
                $btns[] = [['text' => "✅ Tekshirish", 'callback_data' => "check_sub"]];
                send("sendMessage", [
                    'chat_id' => $chat_id,
                    'text' => "Quyidagi kanallarga obuna bo‘ling:",
                    'reply_markup' => json_encode(['inline_keyboard' => $btns])
                ]);
            }
        }
    }

    // 🔹 Admin menyulari
    if ($chat_id == $admin_id) {
        if ($text == "📽 Kinolar") {
            $kb = [
                'keyboard' => [
                    [['text' => '🎞 Kino qo‘shish'], ['text' => '🗑 Kino o‘chirish']],
                    [['text' => '✏ Kino tahrirlash'], ['text' => '◀ Ortga']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📽 Kinolar bo‘limi. Tugma tanlang:",
                'reply_markup' => json_encode($kb)
            ]);
        } elseif ($text == "📢 Xabarlar") {
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "🚧 Xabarlar bo‘limi hozircha tayyor emas."
            ]);
        } elseif ($text == "👨‍💻 Adminlar") {
            $kb = [
                'keyboard' => [
                    [['text' => '➕ Admin qo‘shish'], ['text' => '➖ Admin o‘chirish']],
                    [['text' => '📃 Adminlar ro‘yxati'], ['text' => '◀ Ortga']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "👨‍💻 Adminlar bo‘limi.",
                'reply_markup' => json_encode($kb)
            ]);
        } elseif ($text == "⭐ Super Userlar") {
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "🚧 Super Userlar bo‘limi hozircha tayyor emas."
            ]);
        } elseif ($text == "📡 Kanallar") {
            $kb = [
                'keyboard' => [
                    [['text' => '➕ Kanal qo‘shish'], ['text' => '➖ Kanal o‘chirish']],
                    [['text' => '📃 Kanallar ro‘yxati'], ['text' => '◀ Ortga']]
                ],
                'resize_keyboard' => true
            ];
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📡 Kanallar bo‘limi.",
                'reply_markup' => json_encode($kb)
            ]);
        } elseif ($text == "📊 Statistika") {
            $admins = $db->query("SELECT COUNT(*) FROM adminlar")->fetchColumn();
            $kinolar = $db->query("SELECT COUNT(*) FROM kinolar")->fetchColumn();
            send("sendMessage", [
                'chat_id' => $chat_id,
                'text' => "📊 Statistika:\n👨‍💻 Adminlar: $admins\n🎞 Kinolar: $kinolar"
            ]);
        }

        // 🔹 Tugmalar holatini yozish
        elseif ($text == "➕ Admin qo‘shish") {
            file_put_contents("state.txt", "add_admin");
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "Yangi admin user ID kiriting:"]);
        } elseif ($text == "➖ Admin o‘chirish") {
            file_put_contents("state.txt", "delete_admin");
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "O‘chirish uchun user ID kiriting:"]);
        } elseif ($text == "📃 Adminlar ro‘yxati") {
            $a = $db->query("SELECT user_id FROM adminlar")->fetchAll(PDO::FETCH_COLUMN);
            $t = implode("\n", $a) ?: "🚫 Adminlar yo‘q.";
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "📃 Adminlar:\n$t"]);
        } elseif ($text == "➕ Kanal qo‘shish") {
            file_put_contents("state.txt", "add_channel");
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "Yangi kanal username kiriting:"]);
        } elseif ($text == "➖ Kanal o‘chirish") {
            file_put_contents("state.txt", "delete_channel");
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "O‘chirish uchun kanal username kiriting:"]);
        } elseif ($text == "📃 Kanallar ro‘yxati") {
            $c = $db->query("SELECT username FROM kanallar")->fetchAll(PDO::FETCH_COLUMN);
            $t = implode("\n", $c) ?: "🚫 Kanallar yo‘q.";
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "📃 Kanallar:\n$t"]);
        }
    }

    // 🔹 Tugma holatini bajarish
    elseif (file_exists("state.txt")) {
        $state = file_get_contents("state.txt");
        unlink("state.txt");
        if ($state == "add_admin") {
            $stmt = $db->prepare("INSERT OR IGNORE INTO adminlar (user_id) VALUES (?)");
            $stmt->execute([$text]);
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "✅ Admin qo‘shildi!"]);
        } elseif ($state == "delete_admin") {
            $stmt = $db->prepare("DELETE FROM adminlar WHERE user_id = ?");
            $stmt->execute([$text]);
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "🗑 Admin o‘chirildi."]);
        } elseif ($state == "add_channel") {
            $stmt = $db->prepare("INSERT OR IGNORE INTO kanallar (username) VALUES (?)");
            $stmt->execute([$text]);
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "✅ Kanal qo‘shildi!"]);
        } elseif ($state == "delete_channel") {
            $stmt = $db->prepare("DELETE FROM kanallar WHERE username = ?");
            $stmt->execute([$text]);
            send("sendMessage", ['chat_id' => $chat_id, 'text' => "🗑 Kanal o‘chirildi."]);
        }
    }
}

// 🔹 Callback Tekshirish
if ($callback) {
    $cid = $callback["message"]["chat"]["id"];
    $mid = $callback["message"]["message_id"];
    $data = $callback["data"];
    if ($data == "check_sub") {
        $chs = $db->query("SELECT username FROM kanallar")->fetchAll(PDO::FETCH_COLUMN);
        if (isSubscribed($cid, $chs)) {
            send("editMessageText", [
                'chat_id' => $cid,
                'message_id' => $mid,
                'text' => "✅ Obuna tasdiqlandi. Kino kodini yuboring:"
            ]);
        } else {
            send("answerCallbackQuery", [
                'callback_query_id' => $callback["id"],
                'text' => "❌ Hali barcha kanallarga obuna bo‘lmadingiz!",
                'show_alert' => true
            ]);
        }
    }
}
