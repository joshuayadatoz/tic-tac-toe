<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use TTT\Game;

require dirname(__DIR__) . '/backend/vendor/autoload.php';
error_reporting(E_ALL & ~E_DEPRECATED);
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Game()
        )
    ),
    8080
);

$server->run();
