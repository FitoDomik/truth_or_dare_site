<?php
/**
 * Отправляет уведомление в Telegram
 * @param string $message Текст сообщения
 * @return bool Результат отправки
 */
function sendTelegramNotification($message) {
    $botToken = '';
    $chatId = '';
    $telegramApiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $params = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    $ch = curl_init($telegramApiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('Ошибка отправки уведомления в Telegram: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    $response = json_decode($result, true);
    if (isset($response['ok']) && $response['ok'] === true) {
        return true;
    } else {
        error_log('Ошибка отправки уведомления в Telegram: ' . json_encode($response));
        return false;
    }
}
?> 