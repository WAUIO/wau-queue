<?php

$bus = require_once __DIR__ . "/init.php";

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Channel;
use WAUQueue\Adapter\RabbitMQ\Queue\RandomQueue;
use WAUQueue\Worker;

$bus->bind($exchange,
    new RandomQueue($bus->channel(), [
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => true,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ]), $severities
);

$worker = new Worker($bus);
$worker->setCallback(function(AMQPMessage $message) {
    sleep(rand(0, 3));
    echo '[' . date('Y-m-d H:i:s') . '][',$message->delivery_info['routing_key'], '] ', $message->body, "\n";
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
});

$worker->setBehavior(function(Channel $channel) {
    $channel->get()->basic_qos(null, 1, null);
});

$worker->setProperty('arguments', array('x-priority' => array('I', 10)));

$worker->listen($bus->channel());