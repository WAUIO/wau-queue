<?php

$bus = require_once __DIR__ . "/init.php";

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Channel;
use WAUQueue\Adapter\RabbitMQ\Queue\RandomQueue;
use WAUQueue\Adapter\RabbitMQ\Queue\NamedQueue;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\Job\AbstractJob;
use WAUQueue\Worker;

class DefaultJob extends AbstractJob
{

    public function fire($message) {
        global $bus;
        
        $duration = rand(0, 2);
        print_r("-----------------------------------------------------\nProcessing... wait {$duration} secs\n");
        sleep($duration);
        echo '[' . date('Y-m-d H:i:s') . '][',$message->delivery_info['routing_key'], '] ', $message->body, "\n";
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        
        list($queueId, $mc, $cc) = $this->queue->getInfo();
    
        if($mc > 10 && $cc < 10) {
            print_r("**** Need new consumer for less charge\n");
            $bus->add($this->worker, $this->queue)
                ->setJob(get_called_class())
                ->consume($bus->prop('consumer.strategy', []))
            ;
        }
        
        print_r(array(
            $queueId => array(
                'count.messages'  => $mc,
                'count.consumers' => $cc,
            )
        ));
    }
    
}

$bus->bind($exchange,
    new RandomQueue($bus->channel(), [
        '__prefix'    => 'logs.',
        '__job'       => 'DefaultJob',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => true,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ]), ['error', 'warning']
);

$bus->bind($exchange,
    new RandomQueue($bus->channel(), [
        '__prefix'    => 'logs.',
        '__job'       => 'DefaultJob',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => true,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ]), 'info'
);

$bus->setProperty('consumer.strategy', array(
    'arguments' => array('x-priority' => array('I', 10))
));

$worker = new Worker($bus);
$worker->prefetch(1);

$worker->listen($bus->channel());