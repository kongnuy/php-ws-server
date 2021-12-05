<?php
require __DIR__ . '/vendor/autoload.php';

use MyApp\MyServer;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SecureServer as SecureReactor;
use React\Socket\Server as Reactor;

class IoSecureServer extends IoServer {
  public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0', $secureOptions = []) {
    $loop = LoopFactory::create();
    $socket = new Reactor($address . ':' . $port, $loop);

    if (!empty($secureOptions) && is_array($secureOptions)) {
      $socket = new SecureReactor($socket, $loop, $secureOptions);
    }

    return new static($component, $socket, $loop);
  }
}

$server = IoSecureServer::factory(
  new HttpServer(
    new WsServer(
      new MyServer()
    )
  )
);
$server->run();
