<?php

require_once dirname(dirname(__FILE__)) . "/vendor/autoload.php";

use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Exchange\DirectExchange;
use WAUQueue\Adapter\RabbitMQ\BrokerServiceBuilder;
use WAUQueue\Adapter\RabbitMQ\Connector;

$severities = array_slice($argv, 1);
if (empty($severities)) {
    file_put_contents('php://stderr',
        "Usage: $argv[0] [info] [warning] [error]\n"
    );
    exit(1);
}

$bus = new BrokerServiceBuilder(
    new Connector(array(
        'host'     => 'localhost',
        'port'     => 5672,
        'user'     => 'guest',
        'password' => 'guest',
        'vhost'    => 'portal',
    ))
);

$exchange = $bus->setExchange(
    new DirectExchange($bus->channel(), [
        'name'        => 'direct_logs',
        'passive'     => false,
        'durable'     => false,
        'auto_delete' => false,
        '__arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ])
);

return $bus;