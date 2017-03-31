<?php

require dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

$queue = 'test';
$exchange = 'sms';


//$connect = new RabbitMQConnnector();

$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'sms';
$configs['queue_declare_bind' ] = 'sms';
//$channel = $connect->connect($configs);

$worker = new WAUQueue\RabbitMQWorker($configs);
$worker->listen($queue, $delay = 0, 128, 3, 0, [
    'binding_queue' => 'test',
    'exchange'      => 'sms'
]);


