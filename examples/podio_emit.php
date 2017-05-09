<?php

define('FOR_WORKER', false);

$bus = require_once __DIR__ . "/init.php";
require __DIR__ . "/podio_init.php";

$appId = isset($argv[ 1 ]) ? (Int)$argv[ 1 ] : false;
if(!$appId){
    exit("No App Id. provided\n");
}

use WAUQueue\Payload\JsonPayload;
use WAUQueue\Publisher;

print_r("Ctrl + C to stop propagation\n");
$items = PodioItem::filter($appId, array('limit' => 500));

$publisher = new Publisher($bus);

foreach ($items as $item) {
    $task = ['sync', 'savemedia', 'comment'][ rand(0, 2) ];
    $priority = rand(1, 10);
    
    $message = new JsonPayload([
        'item_id'  => $item->item_id,
        'title'    => $item->title,
        'priority' => $priority,
        'time'     => microtime(true),
    ], [
        'delivery_mode'       => 2,
        'priority'            => $priority,
    ], ['routing' => $task]);
    
    $publisher->publish($message, 'portal.main');
    
    echo " [x] Sent ", $task, ':', $message->raw(), " \n";
}

$bus->close();