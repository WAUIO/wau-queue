<?php

/*
$process = 1;

do {
    print_r("Current : PID=" . getmypid() . "\n");
    $pid = pcntl_fork();
    if($pid <= 0){
        exit("is a child process\n");
    }
    
    print_r("Processes : {$process}\n");
    
    $process++;
    sleep(5);
} while ($pid > 0 && $process < 5);
exit("Stop\n");
*/

$bus = require_once __DIR__ . "/init.php";
require __DIR__ . "/podio_init.php";

use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Queue\RandomQueue;
use WAUQueue\Adapter\RabbitMQ\Queue\NamedQueue;
use WAUQueue\Contracts\Job\AbstractJob;
use WAUQueue\Worker;

abstract class PodioJob extends AbstractJob
{

    public function fire($message) {
        $time = microtime(true);
        global $bus, $argv;
    
        print_r("----------------------------------------------------------------------------------------------------------\n");
        
        $rateLimit = Podio::$last_response->headers['x-rate-limit-remaining'];
        
        $over = $this->worker->module('WAUQueue\Module\RateLimitBalancer@balance', [$rateLimit]);
        
        // stop the script, just do nothing
        if($over) {
            $this->output(">>> Schedule for next hour");
            return;
        }
        
        $this->worker->module('WAUQueue\Module\ConsumerPrefetchBalancer@balance', [$bus, $this->queue, get_called_class(), $message->delivery_info['consumer_tag']]);
        
        $item = json_decode($message->body);
        $item = PodioItem::get($item->item_id);
    
        $elapsed = microtime(true) - $time;
        $this->output("[" . date('Y-m-d H:i:s') . "][{$message->delivery_info['routing_key']}] {$message->body}, Duration={$elapsed}");
        
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }
    
}

class TaskJob extends PodioJob { protected $prefix = '[Podio.Task]';}
class MediaJob extends PodioJob { protected $prefix = '[Podio.Media]'; }
class CommentJob extends PodioJob { protected $prefix = '[Podio.Comment]'; }

class ExampleModule extends \WAUQueue\Contracts\Module\ModuleAbstract {
    
    public function __construct() {
        $this->prefix = '[module.ExampleModule] ';
        $this->registerDefaultStyles();
    }
}

/**
 * make sure to have [exclusive] option set to false to allowing multiple
 * workers on one queue, it will just create new channel for consumers
 */

$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__.prefix'    => 'podio.',
        '__.job'       => 'TaskJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'podio.task'), 'task'
);

$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__.prefix'    => 'podio.',
        '__.job'       => 'MediaJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'podio.savemedia'), 'savemedia'
);

$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__.prefix'    => 'podio.',
        '__.job'       => 'CommentJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'podio.comment'), 'comment'
);

$bus->setProperty('consumer.strategy', array(
    'arguments' => array('x-priority' => array('I', 10))
));

$worker = new Worker($bus, array(
    new ExampleModule(),
    new \WAUQueue\Module\RateLimitBalancer(Podio::$last_response->headers['x-rate-limit-limit'], 10),
    new \WAUQueue\Module\ConsumerPrefetchBalancer('auto', 20, 10),
));
$worker->prefetch(1);

$worker->listen($bus->channel());