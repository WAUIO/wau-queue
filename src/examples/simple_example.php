<?php
//require_once dirname(__FILE__). '../';
require_once dirname(dirname(dirname(__FILE__))) .'/vendor/autoload.php';

use WAUQueue\Connectors\RabbitMQConnnector;
use Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__);

$configs = require dirname(__FILE__) .'/ampq.php';
$configs['exchange_declare']  = 'email';
$configs['queue_declare_bind' ] = 'email';
$connect = new RabbitMQConnnector();
$channel = $connect->connect($configs);

$channel->push('email', array( 'message' => 'First mail') , $configs['queue']);