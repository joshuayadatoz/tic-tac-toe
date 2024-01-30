
//check server ip address
const wsAddress = (window.location.hostname === 'localhost') ? 'ws://localhost:8080' : 'ws://51.20.189.65:8080';
const conn = new WebSocket(wsAddress);

conn.onopen = function(e) {
    console.log("Connection established!");
};

conn.onmessage = function(e) {
    const data = JSON.parse(e.data);
    console.log(data.type);
    switch (data.type) {
        case 'gameCreated':
            prepareGame(data);
            console.log(`Game created successfully. Game ID: ${data.gameId}`);
           
            break;
        case 'moveMade':
            updateBoard(data.index, data.player);
            break;
        case 'turn':
            document.getElementById('turnIndicator').textContent = `Turn: ${data.player}`;
            break;
        case 'joinedGame':
            prepareGame(data);
            enableCells();
            console.log(`Joined game ${data.gameId}`);
            break;
        case 'gameOver':
            updateBoard(data.index, data.player);
            document.getElementById('turnIndicator').textContent =`Game Over. Winner: ${data.winner}`;
            document.getElementById('playAgainButton').classList.remove('hidden');
            document.getElementById('resetButton').classList.remove('hidden');
            break;
        case 'quitGame':
            resetGame();
            break;
        case 'gameReset':
            resetGameUI(data);
            updateTurnIndicator(data.player);
            break;
        case 'error':
            console.error(data.message);
            alert(`Error: ${data.message}`);
            break;
        case 'welcome':
            //alert(data.message);
            break;
        default:
            console.error(data);
            console.log("Unknown message type!");
            break;
    }
};

function prepareGame(data) {

    document.getElementById('gameSetup').classList.add('hidden');
    document.getElementById('gameBoard').classList.remove('hidden');
    document.getElementById('turnIndicator').classList.remove('hidden');
    document.getElementById('gameIdDisplay').classList.remove('hidden');
    document.getElementById('gameId').textContent = data.gameId;
    document.getElementById('turnIndicator').textContent = "Turn: X";
}

function createGame() {
    conn.send(JSON.stringify({type: 'create'}));
}
function resetGameUI() {
    enableCells();
    document.getElementById('playAgainButton').classList.add('hidden');
    document.getElementById('resetButton').classList.add('hidden');
}

function enableCells() {
    const cells = document.querySelectorAll('.cell');
    cells.forEach(cell => {
        cell.textContent = '';
        cell.setAttribute('onclick', 'makeMove(this.dataset.index)');
    });
}

function updateTurnIndicator(player) {
    document.getElementById('turnIndicator').textContent = `Turn: ${player}`;
    document.getElementById('turnIndicator').classList.remove('hidden');
}
function joinGame() {
    const gameId = document.getElementById('gameIdInput').value.trim();
    if (gameId) {
        conn.send(JSON.stringify({type: 'join', gameId: gameId}));
    } else {
        alert('Please enter a Game ID to join.');
    }
}
function playAgain() {
    conn.send(JSON.stringify({type: 'resetGame'}));
    const cells = document.querySelectorAll('.cell');
    cells.forEach(cell => {
        cell.textContent = '';
        cell.removeAttribute('onclick')
    });
}

function makeMove(index) {
    conn.send(JSON.stringify({type: 'move', index: index}));
}

function updateBoard(index, player) {
    const playerSymbol = player; 
    const cells = document.querySelectorAll('.cell');
    document.getElementById('turnIndicator').textContent = `Turn: ${(playerSymbol === 'X') ? 'O' : 'X'}`;
    if (cells[index]) {
        cells[index].textContent = playerSymbol;
        cells[index].removeAttribute('onclick'); 
    }
}
function goToMainScreen() {
    conn.send(JSON.stringify({type: 'quitGame'}));
}

function resetGame() {

    
    document.getElementById('gameSetup').classList.remove('hidden');
    document.getElementById('gameBoard').classList.add('hidden');
    document.getElementById('gameIdDisplay').classList.add('hidden');
    document.getElementById('turnIndicator').classList.add('hidden');
    document.getElementById('playAgainButton').classList.add('hidden');
    document.getElementById('resetButton').classList.add('hidden');
    
    document.getElementById('gameIdInput').value = ''; // Clear the input field for Game ID
}


document.querySelectorAll('.cell').forEach((cell, index) => {
    cell.setAttribute('data-index', index);
});
