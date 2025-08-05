<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');
$type = isset($_GET['type']) ? $_GET['type'] : 'truth';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'easy';
if (!in_array($type, ['truth', 'dare'])) {
    echo json_encode(['error' => 'Неверный тип вопроса']);
    exit;
}
if (!in_array($difficulty, ['easy', 'medium', 'hard', 'illegal'])) {
    echo json_encode(['error' => 'Неверный уровень сложности']);
    exit;
}
$used_questions = [];
if ($type == 'truth') {
    $used_questions = isset($_SESSION['used_truth_questions']) ? $_SESSION['used_truth_questions'] : [];
} else {
    $used_questions = isset($_SESSION['used_dare_tasks']) ? $_SESSION['used_dare_tasks'] : [];
}
$sql = "SELECT id, text FROM questions 
        WHERE type = ? AND difficulty = ? AND is_approved = 1";
if (!empty($used_questions)) {
    $placeholders = implode(',', array_fill(0, count($used_questions), '?'));
    $sql .= " AND id NOT IN ($placeholders)";
}
$sql .= " ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($sql);
if (!empty($used_questions)) {
    $types = "ss" . str_repeat("i", count($used_questions));
    $params = array_merge([$types, $type, $difficulty], $used_questions);
    call_user_func_array([$stmt, 'bind_param'], $params);
} else {
    $stmt->bind_param("ss", $type, $difficulty);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    if ($type == 'truth') {
        $_SESSION['used_truth_questions'] = [];
    } else {
        $_SESSION['used_dare_tasks'] = [];
    }
    $stmt = $conn->prepare("SELECT id, text FROM questions 
                           WHERE type = ? AND difficulty = ? AND is_approved = 1 
                           ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("ss", $type, $difficulty);
    $stmt->execute();
    $result = $stmt->get_result();
}
if ($result->num_rows == 0) {
    $default_questions = [
        'truth' => [
            'easy' => [
                'Какой самый неловкий момент был в твоей жизни?',
                'Кто тебе нравится в этой комнате?',
                'Какой твой самый большой страх?'
            ],
            'medium' => [
                'Расскажи о своем первом поцелуе',
                'Какая самая безумная вещь, которую ты делал в жизни?',
                'Какой твой самый большой секрет?'
            ],
            'hard' => [
                'Расскажи о своем самом постыдном моменте',
                'Что бы ты изменил в своей жизни, если бы мог?',
                'Какая твоя самая странная фантазия?'
            ],
            'illegal' => [
                'Расскажи о самом безумном поступке, который ты совершил',
                'Какая самая дикая мысль приходила тебе в голову?',
                'Расскажи о своем самом странном опыте'
            ]
        ],
        'dare' => [
            'easy' => [
                'Изобрази животное на выбор группы',
                'Спой песню',
                'Станцуй под любимую песню'
            ],
            'medium' => [
                'Позвони другу и скажи, что любишь его/её',
                'Сделай 20 приседаний',
                'Расскажи анекдот'
            ],
            'hard' => [
                'Отдай свой телефон человеку справа на 5 минут',
                'Покажи последние 5 фото в твоей галерее',
                'Напиши сообщение бывшему/бывшей (но не отправляй)'
            ],
            'illegal' => [
                'Сними обувь до конца игры',
                'Выпей стакан воды залпом',
                'Изобрази сцену из фильма'
            ]
        ]
    ];
    $random_index = array_rand($default_questions[$type][$difficulty]);
    $question = $default_questions[$type][$difficulty][$random_index];
    echo json_encode(['question' => $question, 'id' => 0]);
    exit;
}
$row = $result->fetch_assoc();
$question_id = $row['id'];
$question_text = $row['text'];
if ($type == 'truth') {
    if (!isset($_SESSION['used_truth_questions'])) {
        $_SESSION['used_truth_questions'] = [];
    }
    $_SESSION['used_truth_questions'][] = $question_id;
} else {
    if (!isset($_SESSION['used_dare_tasks'])) {
        $_SESSION['used_dare_tasks'] = [];
    }
    $_SESSION['used_dare_tasks'][] = $question_id;
}
echo json_encode(['question' => $question_text, 'id' => $question_id]);
$stmt->close();
?> 