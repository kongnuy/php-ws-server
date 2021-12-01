<?php
require __DIR__ . '/vendor/autoload.php';

use MyApp\MyServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new MyServer()
    )
  ),
  8016
);
$server->run();