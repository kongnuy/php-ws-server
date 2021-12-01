<?php
namespace MyApp;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class MyServer implements MessageComponentInterface {
  protected $clients;
  public $http, $data;

  public function __construct() {
    $this->clients = new \SplObjectStorage;
    $this->http = new RestClient;
  }

  public function onOpen(ConnectionInterface $conn) {
    // Store the new connection to send messages to later
    try {
      $queryString = $conn->httpRequest->getUri()->getQuery();
      parse_str($queryString, $query);
      $data = $this->http->getUserBySession($query['token']);
      if ($data) {
        $this->data = $data;
        $conn->data = $data;
        $this->clients->attach($conn);
        $this->http->updateConnection($conn->resourceId, $data['userID']);
        echo "New connection! ({$data['username']})\n";
      }
    } catch (\Throwable $th) {
      echo "Error ! " . $th->__toString();
    }
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    $numRecv = count($this->clients) - 1;
    echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
      , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

    $data = json_decode($msg, true);

    $sendTo = $this->http->userData($data['sendTo']);

    $send['sendTo'] = $sendTo;
    $send['by'] = $from->data['userID'];
    $send['profileImage'] = $from->data['profileImage'];
    $send['username'] = $from->data['username'];
    $send['type'] = $data['type'];
    $send['data'] = $data['data'];

    foreach ($this->clients as $client) {
      if ($from !== $client) {
        // The sender is not the receiver, send to each client connected
        if ($client->resourceId == $sendTo['connectionID'] || $from == $client) {
          $client->send(json_encode($send));
        }
      }
    }
  }

  public function onClose(ConnectionInterface $conn) {
    // The connection is closed, remove it, as we can no longer send it messages
    $this->clients->detach($conn);

    echo "Connection {$conn->resourceId} has disconnected\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "An error has occurred: {$e->getMessage()}\n";

    $conn->close();
  }
}