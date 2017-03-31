<?php

require dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'texts';
$configs['queue_declare_bind' ] = 'texts';
$configs['exchange_params']['name'] = 'sms_urgent'; // Routing name

$worker = new WAUQueue\RabbitMQWorker($configs);
$worker->listen($configs['queue'], $delay = 0, 128, 3, 0, [
    'binding_queue_route' => 'sms_urgent',
    'exchange'      => 'texts'
]);


