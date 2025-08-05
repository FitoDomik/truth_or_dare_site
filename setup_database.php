<?php
$servername = "localhost";
$username = "u3196694_pravdaidelo";  
$password = "u3196694_pravdaidelo";  
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$sql = "CREATE DATABASE IF NOT EXISTS u3196694_pravdaidelo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "База данных создана успешно или уже существует<br>";
} else {
    echo "Ошибка создания базы данных: " . $conn->error . "<br>";
    exit;
}
$conn->select_db("u3196694_pravdaidelo");
$sql = "CREATE TABLE IF NOT EXISTS questions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    type ENUM('truth', 'dare') NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard', 'illegal') NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Таблица вопросов создана успешно или уже существует<br>";
} else {
    echo "Ошибка создания таблицы вопросов: " . $conn->error . "<br>";
}
$result = $conn->query("SELECT COUNT(*) as count FROM questions");
$row = $result->fetch_assoc();
$count = $row['count'];
if ($count == 0) {
    $initial_questions = [
        ["Какой твой любимый фильм?", "truth", "easy"],
        ["Какое твое самое смущающее воспоминание из школы?", "truth", "easy"],
        ["Какая твоя любимая еда?", "truth", "easy"],
        ["Какой самый странный сон ты видел?", "truth", "easy"],
        ["Что бы ты сделал, если бы выиграл миллион?", "truth", "easy"],
        ["Какой самый большой обман ты совершил?", "truth", "medium"],
        ["Какой самый глупый поступок ты совершил из-за влюбленности?", "truth", "medium"],
        ["Какой момент в жизни ты хотел бы стереть из памяти?", "truth", "medium"],
        ["Какой самый странный комплимент ты получал?", "truth", "medium"],
        ["Что ты никогда не признавался своим родителям?", "truth", "medium"],
        ["Какой самый большой секрет ты скрываешь от присутствующих?", "truth", "hard"],
        ["Какой самый неловкий момент был в твоей интимной жизни?", "truth", "hard"],
        ["О чем ты больше всего сожалеешь в своей жизни?", "truth", "hard"],
        ["Какая самая неприятная черта характера у тебя?", "truth", "hard"],
        ["Что ты сделал такого, о чем до сих пор стыдно?", "truth", "hard"],
        ["Какой самый безумный поступок ты совершил?", "truth", "illegal"],
        ["Какая самая дикая фантазия у тебя есть?", "truth", "illegal"],
        ["Какой самый рискованный поступок ты совершил?", "truth", "illegal"],
        ["Что бы ты сделал, если бы точно знал, что тебя не поймают?", "truth", "illegal"],
        ["Какой самый странный опыт у тебя был?", "truth", "illegal"],
        ["Изобрази животное на выбор группы", "dare", "easy"],
        ["Спой припев из любимой песни", "dare", "easy"],
        ["Сделай 10 приседаний", "dare", "easy"],
        ["Покажи свою лучшую танцевальную движуху", "dare", "easy"],
        ["Изобрази известного актера или актрису", "dare", "easy"],
        ["Позвони другу и скажи, что любишь его/её", "dare", "medium"],
        ["Покажи последние 5 фотографий в твоей галерее", "dare", "medium"],
        ["Отправь сообщение человеку, с которым давно не общался", "dare", "medium"],
        ["Расскажи смешную историю из своей жизни", "dare", "medium"],
        ["Поменяйся одеждой с человеком справа на 10 минут", "dare", "medium"],
        ["Дай доступ к своему телефону человеку слева на 2 минуты", "dare", "hard"],
        ["Напиши сообщение своему бывшему/бывшей (но не отправляй)", "dare", "hard"],
        ["Покажи свой самый смущающий танец", "dare", "hard"],
        ["Позволь группе написать статус в твоих соцсетях", "dare", "hard"],
        ["Сделай комплимент каждому в комнате", "dare", "hard"],
        ["Сними обувь до конца игры", "dare", "illegal"],
        ["Выпей стакан воды залпом", "dare", "illegal"],
        ["Изобрази сцену из фильма на выбор группы", "dare", "illegal"],
        ["Говори с акцентом до следующего хода", "dare", "illegal"],
        ["Изобрази известного певца или певицу", "dare", "illegal"]
    ];
    $stmt = $conn->prepare("INSERT INTO questions (text, type, difficulty, is_approved) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $text, $type, $difficulty);
    foreach ($initial_questions as $question) {
        $text = $question[0];
        $type = $question[1];
        $difficulty = $question[2];
        $stmt->execute();
    }
    echo "Начальные вопросы добавлены успешно<br>";
    $stmt->close();
} else {
    echo "В базе уже есть вопросы, пропускаем добавление начальных данных<br>";
}
echo "<br>Настройка базы данных завершена. <a href='index.php'>Перейти на главную</a>";
$conn->close();
?> 