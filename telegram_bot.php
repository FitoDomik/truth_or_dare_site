<?php
require_once 'db_connect.php';
$bot_token = '7473003280:AAG07jISboXG4pxC5psn2mzynPscdT02qUU';
$api_key = 'tg_secret_key123'; 
$update = json_decode(file_get_contents('php://input'), true);
if (!isset($update['message'])) {
    exit;
}
$message = $update['message'];
$chat_id = $message['chat']['id'];
$text = $message['text'] ?? '';
$from_id = $message['from']['id'];
$admin_ids = [7886808180]; 
$is_admin = in_array($from_id, $admin_ids);
$sessions_file = 'telegram_sessions.json';
$sessions = [];
if (file_exists($sessions_file)) {
    $sessions = json_decode(file_get_contents($sessions_file), true);
}
if (!isset($sessions[$chat_id])) {
    $sessions[$chat_id] = [
        'state' => 'idle',
        'question_type' => null,
        'difficulty' => null,
        'text' => null
    ];
}
function sendMessage($chat_id, $text, $reply_markup = null) {
    global $bot_token;
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $params = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($reply_markup) {
        $params['reply_markup'] = $reply_markup;
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}
function addQuestion($text, $type, $difficulty, $auto_approve = true) {
    global $conn, $api_key;
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/add_question.php';
    $data = [
        'api_key' => $api_key,
        'text' => $text,
        'type' => $type,
        'difficulty' => $difficulty,
        'auto_approve' => $auto_approve
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}
if ($text === '/start') {
    $sessions[$chat_id]['state'] = 'idle';
    $welcome_message = "👋 Привет! Это бот для игры \"Правда или Действие\".\n\n";
    if ($is_admin) {
        $welcome_message .= "Вы являетесь администратором и можете добавлять новые вопросы и задания.\n\n";
        $welcome_message .= "Доступные команды:\n";
        $welcome_message .= "/new - Добавить новый вопрос или задание\n";
        $welcome_message .= "/stats - Посмотреть статистику вопросов\n";
        $welcome_message .= "/cancel - Отменить текущее действие";
    } else {
        $welcome_message .= "Здесь вы можете играть в \"Правда или Действие\" и предлагать свои вопросы и задания.";
    }
    sendMessage($chat_id, $welcome_message);
} elseif ($text === '/new' && $is_admin) {
    $sessions[$chat_id]['state'] = 'awaiting_type';
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Правда', 'callback_data' => 'type_truth'],
                ['text' => 'Действие', 'callback_data' => 'type_dare']
            ]
        ]
    ];
    sendMessage($chat_id, "Выберите тип:", json_encode($keyboard));
} elseif ($text === '/cancel') {
    $sessions[$chat_id]['state'] = 'idle';
    sendMessage($chat_id, "Действие отменено.");
} elseif ($text === '/stats' && $is_admin) {
    $result = $conn->query("SELECT type, difficulty, COUNT(*) as count FROM questions WHERE is_approved = 1 GROUP BY type, difficulty");
    $stats = [
        'total' => 0,
        'truth' => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'illegal' => 0],
        'dare' => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'illegal' => 0]
    ];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['type']][$row['difficulty']] = $row['count'];
            $stats['total'] += $row['count'];
        }
    }
    $stats_message = "📊 <b>Статистика вопросов:</b>\n\n";
    $stats_message .= "Всего вопросов: " . $stats['total'] . "\n\n";
    $stats_message .= "<b>Правда:</b>\n";
    $stats_message .= "Простой: " . $stats['truth']['easy'] . "\n";
    $stats_message .= "Средний: " . $stats['truth']['medium'] . "\n";
    $stats_message .= "Сложный: " . $stats['truth']['hard'] . "\n";
    $stats_message .= "Незаконный: " . $stats['truth']['illegal'] . "\n\n";
    $stats_message .= "<b>Действие:</b>\n";
    $stats_message .= "Простой: " . $stats['dare']['easy'] . "\n";
    $stats_message .= "Средний: " . $stats['dare']['medium'] . "\n";
    $stats_message .= "Сложный: " . $stats['dare']['hard'] . "\n";
    $stats_message .= "Незаконный: " . $stats['dare']['illegal'];
    sendMessage($chat_id, $stats_message);
} elseif (isset($message['callback_query'])) {
    $callback_data = $message['callback_query']['data'];
    if (strpos($callback_data, 'type_') === 0) {
        $type = substr($callback_data, 5);
        $sessions[$chat_id]['question_type'] = $type;
        $sessions[$chat_id]['state'] = 'awaiting_difficulty';
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Простой', 'callback_data' => 'diff_easy'],
                    ['text' => 'Средний', 'callback_data' => 'diff_medium']
                ],
                [
                    ['text' => 'Сложный', 'callback_data' => 'diff_hard'],
                    ['text' => 'Незаконный', 'callback_data' => 'diff_illegal']
                ]
            ]
        ];
        sendMessage($chat_id, "Выберите уровень сложности:", json_encode($keyboard));
    } elseif (strpos($callback_data, 'diff_') === 0) {
        $difficulty = substr($callback_data, 5);
        $sessions[$chat_id]['difficulty'] = $difficulty;
        $sessions[$chat_id]['state'] = 'awaiting_text';
        sendMessage($chat_id, "Теперь введите текст вопроса/действия:");
    }
} else {
    if ($sessions[$chat_id]['state'] === 'awaiting_text' && $is_admin) {
        $sessions[$chat_id]['text'] = $text;
        $result = addQuestion(
            $sessions[$chat_id]['text'],
            $sessions[$chat_id]['question_type'],
            $sessions[$chat_id]['difficulty']
        );
        if ($result && $result['success']) {
            sendMessage($chat_id, "✅ Вопрос успешно добавлен!");
        } else {
            $error = $result['error'] ?? 'Неизвестная ошибка';
            sendMessage($chat_id, "❌ Ошибка при добавлении вопроса: " . $error);
        }
        $sessions[$chat_id]['state'] = 'idle';
    } elseif (!$is_admin) {
        sendMessage($chat_id, "Для предложения вопросов и заданий, пожалуйста, используйте веб-интерфейс: http://" . $_SERVER['HTTP_HOST']);
    }
}
file_put_contents($sessions_file, json_encode($sessions));
?> 