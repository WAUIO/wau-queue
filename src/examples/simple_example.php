<?php
require_once dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

use WAUQueue\Connectors\RabbitMQConnnector;
use Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);

$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'texts';
$configs['queue_declare_bind' ] = 'texts';
$configs['exchange_params']['name'] = 'sms_urgent'; // Routing name

$connect = new RabbitMQConnnector();
$channel = $connect->connect($configs);

$channel->push('sms', array( 'message' => 'Secound SMS') , $configs['queue'], [
    'priority' => 3
]);