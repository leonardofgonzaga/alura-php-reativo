<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

require_once 'vendor/autoload.php';

$chatComponent = new class implements MessageComponentInterface
{
   private SplObjectStorage $connections;

   public function __construct()
   {
      $this->connections = new SplObjectStorage();
   }

   public function onOpen(ConnectionInterface $conn)
   {
      echo 'Nova conexão aceita' . PHP_EOL;
      $this->connections->attach($conn);
   }

   public function onClose(ConnectionInterface $conn)
   {
      echo 'Conexão encerrada' . PHP_EOL;
      $this->connections->detach($conn);
   }

   public function onError(ConnectionInterface $conn, Exception $e)
   {
      echo "Erro: {$e->getTraceAsString()}";
   }

   public function onMessage(ConnectionInterface $from, MessageInterface $msg)
   {
      foreach ($this->connections as $connection) {
         if ($connection !== $from) {
            $connection->send((string) $msg);
         }
      }
   }
};

$server = IoServer::factory(
   new HttpServer(
      new WsServer(($chatComponent))
   ),
   8002
);

$server->run();