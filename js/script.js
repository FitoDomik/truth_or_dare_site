document.addEventListener('DOMContentLoaded', function() {
    const playerForm = document.getElementById('playerForm');
    const addPlayerBtn = document.getElementById('addPlayer');
    if (addPlayerBtn) {
        let playerCount = 2; 
        addPlayerBtn.addEventListener('click', function() {
            playerCount++;
            const playerInputs = document.querySelector('.player-inputs');
            const newPlayerDiv = document.createElement('div');
            newPlayerDiv.className = 'player-input';
            newPlayerDiv.innerHTML = `
                <label for="player${playerCount}">Игрок ${playerCount}:</label>
                <input type="text" id="player${playerCount}" name="players[]" required>
                <button type="button" class="remove-player" aria-label="Удалить игрока">
                    <i class="fas fa-times"></i>
                </button>
            `;
            playerInputs.appendChild(newPlayerDiv);
            const removeBtn = newPlayerDiv.querySelector('.remove-player');
            removeBtn.addEventListener('click', function() {
                playerInputs.removeChild(newPlayerDiv);
            });
            newPlayerDiv.querySelector('input').focus();
        });
    }
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    if (themeRadios.length > 0) {
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.body.className = 'theme-' + this.value;
            });
        });
    }
    if (playerForm) {
        playerForm.addEventListener('submit', function(event) {
            const playerInputs = document.querySelectorAll('input[name="players[]"]');
            const filledInputs = Array.from(playerInputs).filter(input => input.value.trim() !== '');
            if (filledInputs.length < 2) {
                event.preventDefault();
                alert('Для игры нужно минимум два игрока!');
            }
        });
    }
}); 