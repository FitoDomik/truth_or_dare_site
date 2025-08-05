<?php
session_start();
require_once 'db_connect.php';
require_once 'telegram_notification.php';
header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Требуется метод POST']);
    exit;
}
$postData = $_POST;
$jsonData = json_decode(file_get_contents('php://input'), true);
if ($jsonData) {
    $postData = array_merge($postData, $jsonData);
}
$api_key = "tg_secret_key123"; 
$is_api_request = false;
if (isset($postData['api_key']) && $postData['api_key'] === $api_key) {
    $is_api_request = true;
}
if (!isset($postData['text']) || empty(trim($postData['text']))) {
    echo json_encode(['success' => false, 'error' => 'Отсутствует текст вопроса/действия']);
    exit;
}
if (!isset($postData['type']) || !in_array($postData['type'], ['truth', 'dare'])) {
    echo json_encode(['success' => false, 'error' => 'Неверный тип вопроса (должен быть truth или dare)']);
    exit;
}
if (!isset($postData['difficulty']) || !in_array($postData['difficulty'], ['easy', 'medium', 'hard', 'illegal'])) {
    echo json_encode(['success' => false, 'error' => 'Неверный уровень сложности']);
    exit;
}
$auto_approve = false;
if ($is_api_request && isset($postData['auto_approve']) && $postData['auto_approve']) {
    $auto_approve = true;
}
$question_text = trim($postData['text']);
$question_type = $postData['type'];
$difficulty = $postData['difficulty'];
$stmt = $conn->prepare("INSERT INTO questions (text, type, difficulty, is_approved) VALUES (?, ?, ?, ?)");
$is_approved = $auto_approve ? 1 : 0;
$stmt->bind_param("sssi", $question_text, $question_type, $difficulty, $is_approved);
if ($stmt->execute()) {
    $question_id = $stmt->insert_id;
    if (!$auto_approve) {
        $notification_text = "Новый вопрос на модерацию:\n";
        $notification_text .= "Текст: " . $question_text . "\n";
        $notification_text .= "Тип: " . ($question_type == 'truth' ? 'Правда' : 'Действие') . "\n";
        $notification_text .= "Сложность: " . $difficulty;
        sendTelegramNotification($notification_text);
    }
    echo json_encode([
        'success' => true, 
        'message' => $auto_approve ? 'Вопрос успешно добавлен' : 'Вопрос отправлен на модерацию',
        'question_id' => $question_id
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении вопроса: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?> 