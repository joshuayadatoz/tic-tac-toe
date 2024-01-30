<?php

namespace TTT;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Game implements MessageComponentInterface {
    protected $clients;
    private $games = [];
    private $connections = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->connections[$conn->resourceId] = [
            'conn' => $conn,
            'game_id' => null,
            'player' => null
        ];
       
        $conn->send(json_encode(['type' => 'welcome', 'message' => 'Welcome to Tic Tac Toe!']));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
       
        switch ($data['type']) {
            case 'create':
                $gameId = uniqid();
                
                $this->games[$gameId] = [
                    'players' => [$from->resourceId],
                    'board' => array_fill(0, 9, null),
                    'turn' => $from->resourceId,
                    'winner' => null
                ];
                $this->connections[$from->resourceId]['game_id'] = $gameId;
                $this->connections[$from->resourceId]['player'] = 'X';
                $from->send(json_encode(['type' => 'gameCreated', 'gameId' => $gameId]));
                
                break;
            case 'join':
                $gameId = $data['gameId'];
                if (isset($this->games[$gameId]) && count($this->games[$gameId]['players']) < 2) {
                    $this->games[$gameId]['players'][] = $from->resourceId;
                    $this->connections[$from->resourceId]['game_id'] = $gameId;
                    $this->connections[$from->resourceId]['player'] = 'O';
                    
                    foreach ($this->games[$gameId]['players'] as $playerId) {
                        $this->connections[$playerId]['conn']->send(json_encode(['type' => 'joinedGame', 'gameId' => $gameId]));
                    }
                } else {
                    $from->send(json_encode(['type' => 'error', 'message' => 'Unable to join game.']));
                }
                break;
            case 'quitGame':
                $gameId = $this->connections[$from->resourceId]['game_id'];
                if ($gameId) {
                    // Handle player quitting during a game
                    foreach ($this->games[$gameId]['players'] as $playerId) {
                        $this->connections[$playerId]['conn']->send(json_encode([
                            'type' => 'quitGame',
                            'winner' => $this->connections[$playerId]['player'] == 'X' ? 'O' : 'X',
                            'index' => null,
                            'player' => $this->connections[$playerId]['player']
                        ]));
                    }
                    unset($this->games[$gameId]);
                }
                break;
            case 'resetGame':
                $gameId = $this->connections[$from->resourceId]['game_id'];
                if ($gameId) {
                    // Reset the game board
                    $this->games[$gameId]['board'] = array_fill(0, 9, null); // Assuming a 3x3 board
            

                    $startingPlayerId = $this->chooseStartingPlayer($gameId);
            
                    // Set the starting player for the new game
                    $this->games[$gameId]['turn'] = $startingPlayerId;
            
                    // Broadcast 'gameReset' message to both players with the ID of the starting player
                    foreach ($this->games[$gameId]['players'] as $playerId) {
                        $this->connections[$playerId]['conn']->send(json_encode([
                            'type' => 'gameReset',
                            'startingPlayer' => $this->connections[$startingPlayerId]['player'],
                            'gameId' => $gameId 
                        ]));
                        
                    }
                    $nextPlayer = $this->getNextPlayer($gameId, $from->resourceId);
                    $this->games[$gameId]['turn'] = $nextPlayer; // Update the turn in your game state
    
                    // Send a message to both players to update the turn indicator
                    foreach ($this->games[$gameId]['players'] as $playerId) {
                        $this->connections[$playerId]['conn']->send(json_encode([
                            'type' => 'turn',
                            'player' => $this->connections[$nextPlayer]['player'] 
                        ]));
                    }
                }
                break;
                
            case 'move':
                $gameId = $this->connections[$from->resourceId]['game_id'];
                if ($gameId && $this->games[$gameId]['turn'] == $from->resourceId) {
                    $index = $data['index'];
            
                    // Validate move (ensure the cell is empty)
                    if ($this->games[$gameId]['board'][$index] === null) {
                        // Update board with the player's symbol ('X' or 'O')
                        $playerSymbol = $this->connections[$from->resourceId]['player'];
                        $this->games[$gameId]['board'][$index] = $playerSymbol;
            
                        // Check for win or draw
                        $winner = $this->checkForWin($gameId);
                        if ($winner) {
                            // Game Over - Notify players of the winner
                            foreach ($this->games[$gameId]['players'] as $playerId) {

                                $this->connections[$playerId]['conn']->send(json_encode([
                                    'type' => 'gameOver',
                                    'winner' => $winner,
                                    'index' => $index,
                                    'player' => $playerSymbol
                                ]));
                               
                            }
                           
                        } else {
                            // Broadcast the move to both players
                            foreach ($this->games[$gameId]['players'] as $playerId) {
                                $this->connections[$playerId]['conn']->send(json_encode([
                                    'type' => 'moveMade',
                                    'index' => $index,
                                    'player' => $playerSymbol
                                ]));
                            }
            
                            // Switch turns
                            $nextPlayer = $this->getNextPlayer($gameId, $from->resourceId);
                            $this->games[$gameId]['turn'] = $nextPlayer; // Update the turn in your game state
            
                            // Send a message to both players to update the turn indicator
                            foreach ($this->games[$gameId]['players'] as $playerId) {
                                $this->connections[$playerId]['conn']->send(json_encode([
                                    'type' => 'turn',
                                    'player' => $this->connections[$nextPlayer]['player'] 
                                ]));
                            }
                        }
                    }
                }
                break;
            
            
            
        }
    }
    private function chooseStartingPlayer($gameId) {
        return $this->games[$gameId]['players'][array_rand($this->games[$gameId]['players'])];
    }
    
    private function checkForWin($gameId) {
        $board = $this->games[$gameId]['board'];
        $winningCombinations = [
            [0, 1, 2], // Top row
            [3, 4, 5], // Middle row
            [6, 7, 8], // Bottom row
            [0, 3, 6], // Left column
            [1, 4, 7], // Middle column
            [2, 5, 8], // Right column
            [0, 4, 8], // Left to right diagonal
            [2, 4, 6]  // Right to left diagonal
        ];
    
        foreach ($winningCombinations as $combination) {
            if ($board[$combination[0]] !== null &&
                $board[$combination[0]] === $board[$combination[1]] &&
                $board[$combination[1]] === $board[$combination[2]]) {
                return $board[$combination[0]]; 
            }
        }
    
        // Check for draw
        if (!in_array(null, $board)) {
            return 'draw';
        }
    
        return false; // No winner or draw, game continues
    }
    
    
    private function getNextPlayer($gameId, $currentPlayerId) {
        // Return the ID of the other player in the game
        foreach ($this->games[$gameId]['players'] as $playerId) {
            if ($playerId != $currentPlayerId) {
                return $playerId;
            }
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $gameId = $this->connections[$conn->resourceId]['game_id'];
        if ($gameId) {
           
        }
        unset($this->connections[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

 
}