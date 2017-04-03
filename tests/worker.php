<?php

require_once dirname(dirname(__FILE__)) . "/vendor/autoload.php";

use PhpAmqpLib\Message\AMQPMessage;
use WAUQueue\Adapter\RabbitMQ\Exchange\DirectExchange;
use WAUQueue\Adapter\RabbitMQ\BrokerServiceBuilder;
use WAUQueue\Adapter\RabbitMQ\Queue\AutoQueue;
use WAUQueue\Adapter\RabbitMQ\Connector;
use WAUQueue\Adapter\RabbitMQ\Channel;
use WAUQueue\Worker;

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
    ])
);

$bus->bind($exchange,
    new AutoQueue($bus->channel(), [
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => true,
    ]),
    $severities
);

$worker = new Worker($bus);
$worker->setCallback(function(AMQPMessage $message){
    sleep(rand(0, 3));
    echo '[' . date('Y-m-d H:i:s') . '][',$message->delivery_info['routing_key'], '] ', $message->body, "\n";
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
});

$worker->setBehavior(function(Channel $channel) {
    $channel->get()->basic_qos(null, 1, null);
});

$worker->listen($bus->channel());