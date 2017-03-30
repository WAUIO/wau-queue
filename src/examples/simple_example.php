<?php
//require_once dirname(__FILE__). '../';
require_once dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

use WAUQueue\Connectors\RabbitMQConnnector;
use Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);

$queue = 'test';
$configs = require dirname(__FILE__) .'/ampq.php';
$connect = new RabbitMQConnnector();
$channel = $connect->connect($configs);

$channel->push('SendSMS', array( 'message' => 'This is Just a text') , $configs['queue']);