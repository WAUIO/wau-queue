<?php namespace WAUQueue\Module;


use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\Module\ModuleAbstract;
use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Helpers\Utilities;

class ConsumerPrefetchBalancer extends ModuleAbstract
{
    use Utilities;
    
    protected $pid = 1;
    
    const MAX_LIMIT_LEVEL = 50;
    
    protected $level;
    
    protected $up;
    
    protected $down;
    
    protected $actions = array(
        'load.up'     => 'loadUp',
        'load.stable' => null,
        'load.down'   => null,
    );
    
    public function __construct($level, $up = 0, $down = 1) {
        $this->level        = $level;
        $this->up           = $up;
        $this->down         = $down;
        $this->prefix       = '[Module.ConsumerPrefetchBalancer] ';
        $this->registerDefaultStyles();
    }
    
    /**
     * Balancing charge
     *
     * @param BrokerInterface                             $broker
     * @param QueueInterface                              $queue
     * @param                                             $job
     * @param                                             $consumerTag
     */
    public function balance(BrokerInterface $broker, QueueInterface $queue, $job, $consumerTag){
        // get works load level from the queue object
        $json = $queue->status()->json;
        if (isset($json->error)) {
            // nothing to do, info returned error
            $this->output("Error while retrieving queue info [{$json->reason}]", 'error');
            return;
        }
        
        $action = $this->detectAction($json);
        $this->output("[Action={$action}, Ready={$json->messages_ready}, Work={$json->messages}, Consumers={$json->consumers}, Queue={$queue->getName()}, Rate={$json->message_stats->ack_details->rate}%, Tag={$consumerTag}]", 'alert');
    
        // stop if the current consumer is not the master
        $masters = (new CollectionSet($queue->status()->json->consumer_details))->groupBy('channel_details.name')->map(function(CollectionSet $group){
            return $group->values()->last()->consumer_tag;
        });
        
        print_r($masters->toArray());
    
        if(!in_array($consumerTag, $masters->toArray()))
            return;
        
        // fetch the method action and execute it if not null
        $action = $this->array_get($this->actions, $action);
        if(!is_null($action) && method_exists($this, $action)) {
            call_user_func(array($this, $action), $broker, $queue, $job, $consumerTag);
        }
    }
    
    /**
     * Grow up the consumption, but only if the current consumer is the master one
     *
     * @param \WAUQueue\Contracts\Message\BrokerInterface $broker
     * @param \WAUQueue\Contracts\Message\QueueInterface  $queue
     * @param                                             $job
     * @param                                             $consumerTag
     */
    protected function loadUp(BrokerInterface $broker, QueueInterface $queue, $job, $consumerTag) {
        $broker->add($this->context, $queue, $this->context)
            ->setJob($job)
            ->consume($broker->prop('consumer.strategy', []))
        ;
    }
    
    protected function loadDown(BrokerInterface $broker, QueueInterface $queue, $job, $consumerTag) {

    }
    
    protected function __loadDown(BrokerInterface $broker, QueueInterface $queue, $job, $consumerTag) {
        $consumers = (new CollectionSet($queue->status()->json->consumer_details))->whereNot('consumer_tag', $consumerTag);
        if($consumers->count() > 0) {
            $consumer = $consumers->first();
            $removed = $broker->remove($consumer->consumer_tag, true);
            if($removed) {
                $this->output("Consumer [{$consumer->consumer_tag}] removed from channel", 'highlight');
            }
        }
    }
    
    protected function detectAction($json) {
        // if the consumers level hit the max limit just let's going
        if($json->consumers > self::MAX_LIMIT_LEVEL && $json->messages_ready >= $this->up)
            return 'load.stable';
        
        // let's grow the prefetch if consumers still under the level
        if($json->messages_ready > $this->up && ($json->consumers < $this->level || is_null($this->level) || $this->level == 'auto'))
            return 'load.up';
        
        if($json->messages_ready <= $json->messages && ($json->consumers > $this->level / 5 || is_null($this->level) || $this->level == 'auto'))
            return 'load.down';
        
        return 'load.stable';
    }
    
    protected function kill($pid){
        print_r("Killing process {$pid}\n");
        return exec("kill -9 $pid");
    }
}