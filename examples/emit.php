<?php

define('FOR_WORKER', false);

$bus = require_once __DIR__ . "/init.php";

use WAUQueue\Payload\JsonPayload;
use WAUQueue\Publisher;

$publisher = new Publisher($bus);

$times = (Int)$argv[ 1 ];

for ($i = 0; $i <= $times; $i++) {
    $severity = ['info', 'warning', 'error'][ rand(0, 2) ];
    $priority = rand(1, 10);
    
    $message = new JsonPayload([
        'uid'      => md5($i * microtime(true)),
        'priority' => $priority,
        'rank'     => $i,
        'time'     => microtime(true),
    ], [
        'delivery_mode'       => 2,
        'priority'            => $priority,
    ], ['routing' => $severity]);
    
    $publisher->publish($message);
    
    echo " [x] Sent ", $severity, ':', $message->raw(), " \n";
}

$bus->close();