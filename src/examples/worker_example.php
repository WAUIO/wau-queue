<?php

require dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';
use WAUQueue\Connectors\RabbitMQConnnector;

$queue = 'test';
$exchange = 'sms';


$connect = new RabbitMQConnnector();
$configs = require dirname(__FILE__) .'/ampq.php';
$channel = $connect->connect($configs);

$worker = new WAUQueue\RabbitMQWorker($configs);
$worker->listen('rabbitmq', $queue, 0, 120);


