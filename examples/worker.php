<?php

$bus = require_once __DIR__ . "/init.php";

use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Queue\RandomQueue;
use WAUQueue\Adapter\RabbitMQ\Queue\NamedQueue;
use WAUQueue\Contracts\Job\AbstractJob;
use WAUQueue\Worker;

abstract class DefaultJob extends AbstractJob
{

    public function fire($message) {
        global $bus;
        
        $duration = rand(0, 1);
        print_r("-----------------------------------------------------\nProcessing... wait {$duration} secs\n");
        sleep($duration);
        $this->output("[" . date('Y-m-d H:i:s') . "][{$message->delivery_info['routing_key']}] {$message->body}");
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    
        list($queueId, $mc, $cc) = array(
            $this->queue->getName(),
            $this->queue->status()->json->messages,
            $this->queue->status()->json->consumers,
        );
        
        if($mc > 10 && $cc < 10) {
            print_r("**** Need new consumer for less charge\n");
            $bus->add($this->worker, $this->queue, $this->worker)
                ->setJob(get_called_class())
                ->consume($bus->prop('consumer.strategy', []))
            ;
        } elseif($mc < 10 && $cc > 2) {
        
        }
        
        print_r(array(
            $queueId => array(
                'count.messages'  => $mc,
                'count.consumers' => $cc,
            )
        ));
    
        print_r($this->worker->status());
    }
    
}

class ErrorJob extends DefaultJob { protected $defaultStyle = 'error'; }
class WarningJob extends DefaultJob { protected $defaultStyle = 'warning'; }
class InfoJob extends DefaultJob { protected $defaultStyle = 'info'; }

class ExampleModule implements \WAUQueue\Module\ModuleInterface {
    public function __construct() {
    }
}

$bus->bind($exchange,
    new RandomQueue($bus->channel(), [
        '__.prefix'    => 'logs.',
        '__.job'       => 'ErrorJob',
        '__.vhost'       => 'portal',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => true,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'logs.errors'), 'error'
);

/*
$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__prefix'    => 'logs.',
        '__job'       => 'WarningJob',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => true,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'logs.warnings'), 'warning'
);
*/

$bus->bind($exchange,
    new RandomQueue($bus->channel(), [
        '__.prefix'    => 'logs.',
        '__.job'       => 'InfoJob',
        '__.vhost'       => 'portal',
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => true,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'logs.infos'), 'info'
);

$bus->setProperty('consumer.strategy', array(
    'arguments' => array('x-priority' => array('I', 10))
));

$worker = new Worker($bus, array(
    new ExampleModule()
));
$worker->prefetch(2);

$worker->listen($bus->channel());