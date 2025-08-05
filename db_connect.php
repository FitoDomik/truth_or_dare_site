<?php
$servername = "localhost";
$username = "";  
$password = "";  
$dbname = "";    
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$sql_questions = "CREATE TABLE IF NOT EXISTS questions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    type ENUM('truth', 'dare') NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard', 'illegal') NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($sql_questions)) {
    echo "Ошибка создания таблицы questions: " . $conn->error;
}
?> 