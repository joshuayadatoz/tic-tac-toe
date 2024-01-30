<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic Tac Toe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tic Tac Toe</h1>
        <div id="gameSetup" class="game-setup">
            <button class="btn" onclick="createGame()">Create Game</button>
            <p class="or">OR</p>
            <input type="text" id="gameIdInput" class="game-id-input" placeholder="Enter Game ID">
            <button class="btn" onclick="joinGame()">Join Game</button>
        </div>
        <div id="gameIdDisplay" class="game-id-display hidden">Game ID: <span id="gameId"></span></div>
        <div id="turnIndicator" class="turn-indicator hidden">Turn: X</div>
        <div id="gameBoard" class="board hidden">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <div class="cell" data-index="<?= $i ?>"></div>
            <?php endfor; ?>
        </div>
        <button id="playAgainButton" class="btn hidden" onclick="playAgain()">Play Again</button>
  
        <button id="resetButton" class="btn hidden" onclick="goToMainScreen()">Main Screen</button>

        
    </div>
    <script src="script.js"></script>
</body>
</html>
