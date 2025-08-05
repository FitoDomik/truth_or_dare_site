<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['player_index'])) {
    $player_index = intval($_POST['player_index']);
    if (isset($_SESSION['players']) && isset($_SESSION['players'][$player_index])) {
        $_SESSION['current_player_index'] = $player_index;
        echo json_encode(['success' => true, 'player' => $_SESSION['players'][$player_index]]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Игрок не найден']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Неверный запрос']);
}
?> 