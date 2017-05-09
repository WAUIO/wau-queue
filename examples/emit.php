<?php

define('FOR_WORKER', false);

$bus = require_once __DIR__ . "/init.php";

use WAUQueue\Payload\JsonPayload;
use WAUQueue\Publisher;

$publisher = new Publisher($bus);

function loop(Publisher $publisher, $times) {
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
        
        $publisher->publish($message, 'portal.main');
        
        echo " [x] Sent ", $severity, ':', $message->raw(), " \n";
    }
}

print_r("Ctrl + C to stop propagation\n");

$maxTime = isset($argv[ 1 ]) ? (Int)$argv[ 1 ] : 200;
$maxWait = isset($argv[ 2 ]) ? (Int)$argv[ 2 ] : 40;

while (true){
    $times = rand(1, $maxTime);
    $wait = rand(5, $maxWait);
    print_r("About to publishing {$times} messages...\n");
    loop($publisher, $times);
    print_r("Waiting for {$wait} seconds...\n");
    sleep($wait);
}

$bus->close();