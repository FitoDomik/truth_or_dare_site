<?php
session_start();
require_once 'db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $players = isset($_POST['players']) ? array_filter($_POST['players']) : [];
    $difficulty = isset($_POST['difficulty']) ? $_POST['difficulty'] : 'easy';
    $_SESSION['players'] = $players;
    $_SESSION['difficulty'] = $difficulty;
    $_SESSION['current_player_index'] = -1; 
    $_SESSION['used_truth_questions'] = [];
    $_SESSION['used_dare_tasks'] = [];
} else if (!isset($_SESSION['players']) || empty($_SESSION['players'])) {
    header("Location: index.php");
    exit;
}
$players = $_SESSION['players'];
$difficulty = $_SESSION['difficulty'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра - Правда или Действие</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https:
</head>
<body>
    <div class="container">
        <header>
            <h1>Правда или Действие</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="settings.php">Настройки</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div id="player-selection" class="game-screen active">
                <h2>Выбор игрока</h2>
                <div class="player-wheel">
                    <?php foreach ($players as $player): ?>
                        <div class="player-name"><?php echo htmlspecialchars($player); ?></div>
                    <?php endforeach; ?>
                </div>
                <button id="spin-wheel" class="btn-primary">Крутить</button>
            </div>
            <div id="player-turn" class="game-screen">
                <h2>Ход игрока: <span id="current-player-name"></span></h2>
                <div class="choice-buttons">
                    <button id="truth-btn" class="btn-choice truth">Правда</button>
                    <button id="dare-btn" class="btn-choice dare">Действие</button>
                </div>
            </div>
            <div id="question-display" class="game-screen">
                <h2 id="question-type"></h2>
                <div id="question-text" class="question-box"></div>
                <button id="next-turn" class="btn-primary">Следующий ход</button>
            </div>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Правда или Действие</p>
        </footer>
    </div>
    <script>
        const players = <?php echo json_encode($players); ?>;
        const difficulty = "<?php echo $difficulty; ?>";
        const playerSelectionScreen = document.getElementById('player-selection');
        const playerTurnScreen = document.getElementById('player-turn');
        const questionScreen = document.getElementById('question-display');
        const currentPlayerNameEl = document.getElementById('current-player-name');
        const questionTypeEl = document.getElementById('question-type');
        const questionTextEl = document.getElementById('question-text');
        const spinWheelBtn = document.getElementById('spin-wheel');
        const truthBtn = document.getElementById('truth-btn');
        const dareBtn = document.getElementById('dare-btn');
        const nextTurnBtn = document.getElementById('next-turn');
        let currentPlayerIndex = -1;
        function animatePlayerSelection() {
            const playerWheel = document.querySelector('.player-wheel');
            const playerNames = document.querySelectorAll('.player-name');
            playerNames.forEach(name => name.classList.remove('selected', 'highlight'));
            let counter = 0;
            const maxIterations = 20 + Math.floor(Math.random() * 10);
            const interval = 100;
            const animation = setInterval(() => {
                playerNames.forEach(name => name.classList.remove('highlight'));
                const highlightIndex = counter % players.length;
                playerNames[highlightIndex].classList.add('highlight');
                counter++;
                if (counter > maxIterations / 2) {
                    clearInterval(animation);
                    const slowAnimation = setInterval(() => {
                        playerNames.forEach(name => name.classList.remove('highlight'));
                        const highlightIndex = counter % players.length;
                        playerNames[highlightIndex].classList.add('highlight');
                        counter++;
                        if (counter >= maxIterations) {
                            clearInterval(slowAnimation);
                            currentPlayerIndex = counter % players.length;
                            playerNames[currentPlayerIndex].classList.add('selected');
                            setTimeout(() => {
                                playerSelectionScreen.classList.remove('active');
                                playerTurnScreen.classList.add('active');
                                currentPlayerNameEl.textContent = players[currentPlayerIndex];
                                fetch('update_player.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'player_index=' + currentPlayerIndex
                                });
                            }, 1000);
                        }
                    }, interval * 2);
                }
            }, interval);
        }
        function getQuestion(type) {
            fetch(`get_question.php?type=${type}&difficulty=${difficulty}`)
                .then(response => response.json())
                .then(data => {
                    playerTurnScreen.classList.remove('active');
                    questionScreen.classList.add('active');
                    questionTypeEl.textContent = type === 'truth' ? 'Правда' : 'Действие';
                    questionTextEl.textContent = data.question;
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    questionTextEl.textContent = 'Произошла ошибка при загрузке вопроса.';
                });
        }
        spinWheelBtn.addEventListener('click', animatePlayerSelection);
        truthBtn.addEventListener('click', () => getQuestion('truth'));
        dareBtn.addEventListener('click', () => getQuestion('dare'));
        nextTurnBtn.addEventListener('click', () => {
            questionScreen.classList.remove('active');
            playerSelectionScreen.classList.add('active');
        });
    </script>
</body>
</html> 