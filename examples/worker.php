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

use PhpAmqpLib\Wire\AMQPTable;
use WAUQueue\Adapter\RabbitMQ\Queue\NamedQueue;
use WAUQueue\Contracts\Job\AbstractJob;
use WAUQueue\Worker;

abstract class DefaultJob extends AbstractJob
{

    public function fire($message) {
        global $bus, $argv;
    
        $duration = rand(0, 1);
        print_r("----------------------------------------------------------------------------------------------------------\nProcessing... wait {$duration} secs\n");
        sleep($duration);
        
        $storage = __DIR__ . "/../storages/events";
        if(!is_dir($storage)){
            mkdir($storage, 0777);
        }
    
        $rtFile = __DIR__ . "/../storages/rate.limit";
        if (!is_file($rtFile)) {
            $rateLimit = 2000;
        } else {
            $rateLimit = intval(file_get_contents($rtFile));
        }
        
        $over = $this->worker->module('WAUQueue\Module\RateLimitBalancer@balance', [$rateLimit]);
        
        // stop the script, just do nothing
        if($over) {
            $this->output(">>> Schedule for next hour");
            return;
        }
        
        $this->worker->module('WAUQueue\Module\ConsumerPrefetchBalancer@balance', [$bus, $this->queue, get_called_class(), $message->delivery_info['consumer_tag']]);
        
        $this->output("[" . date('Y-m-d H:i:s') . "][{$message->delivery_info['routing_key']}] {$message->body}");
        
        /*
        $body = json_decode($message->body);
        file_put_contents($storage . "/{$body->uid}.json", $message->body);
        */
        
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        //log keen.io
        
        $rateLimit--;
        file_put_contents($rtFile, $rateLimit);
    }
    
}

class ErrorJob extends DefaultJob { protected $defaultStyle = 'error'; }
class WarningJob extends DefaultJob { protected $defaultStyle = 'warning'; }
class InfoJob extends DefaultJob { protected $defaultStyle = 'info'; }

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
        '__.prefix'    => 'logs.',
        '__.job'       => 'ErrorJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'logs.errors'), 'error'
);

$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__.prefix'    => 'logs.',
        '__.job'       => 'WarningJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
        'auto_delete' => false,
        'arguments'   => new AMQPTable(array(
            'x-max-priority' => 10
        )),
    ], 'logs.warnings'), 'warning'
);

$bus->bind($exchange,
    new NamedQueue($bus->channel(), [
        '__.prefix'    => 'logs.',
        '__.job'       => 'InfoJob',
        '__.vhost'       => $config['vhost'],
        'passive'     => false,
        'durable'     => true,
        'exclusive'   => false,
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
    new ExampleModule(),
    new \WAUQueue\Module\RateLimitBalancer(2000, 10),
    new \WAUQueue\Module\ConsumerPrefetchBalancer('auto', 20, 10),
));
$worker->prefetch(1);

$worker->listen($bus->channel());