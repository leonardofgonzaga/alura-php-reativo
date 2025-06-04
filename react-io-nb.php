<?php

use React\EventLoop\Factory;
use React\Stream\DuplexResourceStream;
use React\Stream\ReadableResourceStream;

require_once 'vendor/autoload.php';

$loop = Factory::create();

$streamList = [   
   new ReadableResourceStream(stream_socket_client('tcp://localhost:8001'), $loop),
   new ReadableResourceStream(fopen('arquivo1.txt', 'r'), $loop),
   new ReadableResourceStream(fopen('arquivo2.txt', 'r'), $loop),
];

$hhtp = new DuplexResourceStream(stream_socket_client('tcp://localhost:8080'), $loop);
$http->write('GET /http-server.php HTTP/1.1' . "\r\n\r\n");

$http->on('data', function(string $data) {
   $posicaoFimHttp = strpos($data, "\r\n\r\n");
   echo substr($data, $posicaoFimHttp + 4);
});

foreach ($streamList as $readablrStream) {
   $readablrStream->on('data', function(string $data){
      echo $data . PHP_EOL;
   });
}

$loop->run();