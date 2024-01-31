# Tic Tac Toe Game

This project is a web-based Tic Tac Toe game that enables two players to create, join, and play a game remotely from each other. The game is built with real-time communication using PHP WebSockets, and a basic HTML frontend that compiles SCSS to CSS for styling.

Live demo can be accessed on http://51.20.189.65/tic-tac-toe/frontend/
To play the game, one player starts by creating a game, generating a unique game ID. The second player then joins the game by entering this game ID. 

## Prerequisites

- PHP 8.0 or higher
- Composer for managing PHP dependencies
- Node.js and npm for compiling SCSS files


## Project Structure
```
tic-tac-toe/
│
├── backend/ # Backend source files
│ ├── vendor/ # Composer vendor directory
│ ├── composer.json # Composer dependency file
│ ├── composer.lock # Composer lock file (do not edit)
│ ├── Game.php # Game logic
│ └── websocket.php # WebSocket server script
│
├── frontend/ # Frontend source files
│ ├── node_modules/ # npm modules (do not edit)
│ ├── index.php # Main entry HTML/PHP file
│ ├── script.js # JavaScript for frontend logic
│ ├── style.css # Compiled CSS file
│ ├── style.css.map # CSS map file (for debugging)
│ └── style.scss # SCSS source file
│
└── .gitignore # Specifies intentionally untracked files to ignore
```

## Setup Instructions

### Backend

1. Navigate to the `backend/` directory.
2. Install PHP dependencies with Composer:

   ```
   composer install

### Frontend

1. Navigate to the frontend/ directory.
2. Install npm packages (including SCSS compiler):
   ```
   npm install
3. Compile SCSS to CSS:
    ````
    npm run compile-scss
4. Or to watch for changes while editing/developing the .scss files and compile automatically, run:
    ````
    npm run watch

## Running the Application

# WebSocket Server
1. Within the backend/ directory, start the WebSocket server by running:
    ````
    php websocket.php
2. The server must be running for the game to function.

## Accessing the Game
Open frontend/index.php in a web browser to start playing the game.

# Development
## Making Changes
1. Backend game logic can be modified in backend/Game.php.
2. Frontend appearance can be altered by editing frontend/style.scss and re-compiling to frontend/style.css.
3. Frontend behavior (such as game interactions) can be modified in frontend/script.js.

