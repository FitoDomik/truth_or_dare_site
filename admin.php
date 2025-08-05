<?php
session_start();
require_once 'db_connect.php';
$admin_password = "admin123"; 
$is_admin = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $admin_password) {
        $_SESSION['is_admin'] = true;
    } else {
        $error_message = "Неверный пароль";
    }
}
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $is_admin = true;
}
if ($is_admin && isset($_POST['action']) && isset($_POST['question_id'])) {
    $question_id = intval($_POST['question_id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE questions SET is_approved = 1 WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $message = "Вопрос одобрен";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $message = "Вопрос отклонен";
    }
}
if ($is_admin && isset($_POST['add_question'])) {
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    if (!empty($question_text)) {
        $stmt = $conn->prepare("INSERT INTO questions (text, type, difficulty, is_approved) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $question_text, $question_type, $difficulty);
        if ($stmt->execute()) {
            $message = "Новый вопрос добавлен";
        } else {
            $error_message = "Ошибка при добавлении вопроса";
        }
    } else {
        $error_message = "Пожалуйста, введите текст вопроса";
    }
}
$pending_questions = [];
if ($is_admin) {
    $result = $conn->query("SELECT * FROM questions WHERE is_approved = 0 ORDER BY created_at DESC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pending_questions[] = $row;
        }
    }
}
$stats = [
    'total' => 0,
    'truth' => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'illegal' => 0],
    'dare' => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'illegal' => 0]
];
if ($is_admin) {
    $result = $conn->query("SELECT type, difficulty, COUNT(*) as count FROM questions WHERE is_approved = 1 GROUP BY type, difficulty");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['type']][$row['difficulty']] = $row['count'];
            $stats['total'] += $row['count'];
        }
    }
}
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Администрирование - Правда или Действие</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="theme-<?php echo $theme; ?>">
    <div class="container">
        <header>
            <h1>Правда или Действие</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="settings.php">Настройки</a></li>
                    <li><a href="admin.php" class="active">Админ</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="admin-container">
                <h2>Панель администратора</h2>
                <?php if (!$is_admin): ?>
=
                    <form action="admin.php" method="post" class="admin-login">
                        <h3>Вход в панель администратора</h3>
                        <?php if (isset($error_message)): ?>
                            <div class="alert error"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="admin_password">Пароль:</label>
                            <input type="password" id="admin_password" name="admin_password" required>
                        </div>
                        <button type="submit" class="btn-primary">Войти</button>
                    </form>
                <?php else: ?>
=
                    <?php if (isset($message)): ?>
                        <div class="alert success"><?php echo $message; ?></div>
                    <?php endif; ?>
=
                    <section class="admin-section">
                        <h3>Статистика вопросов</h3>
                        <div class="stats-container">
                            <div class="stats-total">
                                <strong>Всего вопросов:</strong> <?php echo $stats['total']; ?>
                            </div>
                            <div class="stats-grid">
                                <div class="stats-column">
                                    <h4>Правда</h4>
                                    <ul>
                                        <li>Простой: <?php echo $stats['truth']['easy']; ?></li>
                                        <li>Средний: <?php echo $stats['truth']['medium']; ?></li>
                                        <li>Сложный: <?php echo $stats['truth']['hard']; ?></li>
                                        <li>Незаконный: <?php echo $stats['truth']['illegal']; ?></li>
                                    </ul>
                                </div>
                                <div class="stats-column">
                                    <h4>Действие</h4>
                                    <ul>
                                        <li>Простой: <?php echo $stats['dare']['easy']; ?></li>
                                        <li>Средний: <?php echo $stats['dare']['medium']; ?></li>
                                        <li>Сложный: <?php echo $stats['dare']['hard']; ?></li>
                                        <li>Незаконный: <?php echo $stats['dare']['illegal']; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>
=
                    <section class="admin-section">
                        <h3>Добавить новый вопрос</h3>
                        <form action="admin.php" method="post">
                            <div class="form-group">
                                <label for="question_text">Текст вопроса/действия:</label>
                                <textarea id="question_text" name="question_text" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Тип:</label>
                                <div class="radio-options">
                                    <label>
                                        <input type="radio" name="question_type" value="truth" checked>
                                        <span>Правда</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="question_type" value="dare">
                                        <span>Действие</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Сложность:</label>
                                <div class="radio-options">
                                    <label>
                                        <input type="radio" name="difficulty" value="easy" checked>
                                        <span>Простой</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="difficulty" value="medium">
                                        <span>Средний</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="difficulty" value="hard">
                                        <span>Сложный</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="difficulty" value="illegal">
                                        <span>Незаконный</span>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" name="add_question" class="btn-primary">Добавить вопрос</button>
                        </form>
                    </section>
                    <section class="admin-section">
                        <h3>Вопросы на модерацию (<?php echo count($pending_questions); ?>)</h3>
                        <?php if (empty($pending_questions)): ?>
                            <p>Нет вопросов на модерацию</p>
                        <?php else: ?>
                            <div class="pending-questions">
                                <?php foreach ($pending_questions as $question): ?>
                                    <div class="question-card">
                                        <div class="question-info">
                                            <p class="question-text"><?php echo htmlspecialchars($question['text']); ?></p>
                                            <div class="question-meta">
                                                <span class="question-type"><?php echo $question['type'] == 'truth' ? 'Правда' : 'Действие'; ?></span>
                                                <span class="question-difficulty">
                                                    <?php
                                                    $difficulty_names = [
                                                        'easy' => 'Простой',
                                                        'medium' => 'Средний',
                                                        'hard' => 'Сложный',
                                                        'illegal' => 'Незаконный'
                                                    ];
                                                    echo $difficulty_names[$question['difficulty']];
                                                    ?>
                                                </span>
                                                <span class="question-date"><?php echo date('d.m.Y H:i', strtotime($question['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="question-actions">
                                            <form action="admin.php" method="post" class="inline-form">
                                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-approve">Одобрить</button>
                                            </form>
                                            <form action="admin.php" method="post" class="inline-form">
                                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-reject">Отклонить</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Правда или Действие</p>
        </footer>
    </div>
</body>
</html> 