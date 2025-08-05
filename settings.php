<?php
session_start();
require_once 'db_connect.php';
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_question'])) {
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    if (!empty($question_text)) {
        $stmt = $conn->prepare("INSERT INTO questions (text, type, difficulty, is_approved) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $question_text, $question_type, $difficulty);
        if ($stmt->execute()) {
            $message = '<div class="alert success">Спасибо! Ваш вопрос отправлен на проверку.</div>';
            include 'telegram_notification.php';
            sendTelegramNotification("Новый вопрос: " . $question_text . "\nТип: " . $question_type . "\nСложность: " . $difficulty);
        } else {
            $message = '<div class="alert error">Произошла ошибка при отправке вопроса.</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert error">Пожалуйста, введите текст вопроса.</div>';
    }
}
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
if (isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    $_SESSION['theme'] = $theme;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки - Правда или Действие</title>
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
                    <li><a href="settings.php" class="active">Настройки</a></li>
                    <li><a href="admin.php">Админ</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="settings-container">
                <h2>Настройки</h2>
                <?php if (!empty($message)) echo $message; ?>
                <section class="theme-settings admin-section">
                    <h3>Тема оформления</h3>
                    <form action="settings.php" method="post">
                        <div class="theme-options">
                            <label>
                                <input type="radio" name="theme" value="light" <?php echo $theme == 'light' ? 'checked' : ''; ?>>
                                <span>Светлая</span>
                            </label>
                            <label>
                                <input type="radio" name="theme" value="dark" <?php echo $theme == 'dark' ? 'checked' : ''; ?>>
                                <span>Темная</span>
                            </label>
                        </div>
                        <button type="submit" class="btn-secondary">Сохранить тему</button>
                    </form>
                </section>
                <section class="suggest-question admin-section">
                    <h3>Предложить вопрос или действие</h3>
                    <form action="settings.php" method="post">
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
                        <button type="submit" name="submit_question" class="btn-primary">Отправить</button>
                    </form>
                </section>
            </div>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Правда или Действие</p>
        </footer>
    </div>
</body>
</html> 