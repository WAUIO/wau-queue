<?php

require_once dirname(dirname(__FILE__)) . "/vendor/autoload.php";

use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Exchange\DirectExchange;
use WAUQueue\Adapter\RabbitMQ\BrokerServiceBuilder;
use WAUQueue\Adapter\RabbitMQ\Connector;

$config = array(
    //'host'     => 'penguin.rmq.cloudamqp.com',
    'host'     => 'localhost',
    'port'     => 5672,
    //'port.api' => 443,
    'port.api' => 15672,
    //'user'     => 'znnvkwxh',
    'user'     => 'guest',
    //'password' => 'qYtvFZGrMQPxOn1qfurY6jpl8sANBZvs',
    'password' => 'guest',
    //'vhost'    => 'znnvkwxh',
    'vhost'    => '/',
);

$bus = new BrokerServiceBuilder(
    new Connector(array(
        'host'     => $config['host'],
        'port'     => $config['port'],
        'port.api' => $config['port.api'],
        'user'     => $config['user'],
        'password' => $config['password'],
        'vhost'    => $config['vhost'],
    ))
);

$exchange = $bus->setExchange(
    new DirectExchange($bus->channel(), [
        'name'        => 'portal.main',
        'passive'     => false,
        'durable'     => false,
        'auto_delete' => false,
        '__arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ])
);

return $bus;