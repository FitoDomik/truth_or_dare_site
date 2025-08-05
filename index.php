<?php
session_start();
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Правда или Действие</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="theme-<?php echo $theme; ?>">
    <div class="container">
        <header>
            <h1>Правда или Действие</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Главная</a></li>
                    <li><a href="settings.php">Настройки</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="start-game">
                <h2>Начать игру</h2>
                <form action="game.php" method="post" id="playerForm">
                    <div class="player-inputs">
                        <div class="player-input">
                            <label for="player1">Игрок 1:</label>
                            <input type="text" id="player1" name="players[]" required>
                        </div>
                        <div class="player-input">
                            <label for="player2">Игрок 2:</label>
                            <input type="text" id="player2" name="players[]" required>
                        </div>
                    </div>
                    <button type="button" id="addPlayer" class="btn-secondary">Добавить игрока</button>
                    <div class="difficulty">
                        <h3>Уровень сложности:</h3>
                        <div class="difficulty-options">
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
                    <button type="submit" class="btn-primary">Начать игру</button>
                </form>
            </div>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Правда или Действие</p>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html> 