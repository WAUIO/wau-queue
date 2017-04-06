<?php namespace WAUQueue;


use WAUQueue\Contracts\Client\ConsumerAbstract;
use WAUQueue\Contracts\Client\ConsumerInterface;
use WAUQueue\Contracts\Client\ConsumersSet;
use WAUQueue\Contracts\Client\WorkerInterface;
use WAUQueue\Contracts\ConsumersSupportInterface;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Helpers\PropertiesTrait;
use WAUQueue\Module\ModulableInterface;
use WAUQueue\Module\ModulableHelperTrait;
use WAUQueue\Module\ModuleInterface;

/**
 * Class Worker
 *
 * Basic Worker that should be compatibe with all type of Broker
 *
 * @package WAUQueue
 */
class Worker implements WorkerInterface, ModulableInterface, ConsumersSupportInterface
{
    use PropertiesTrait, ModulableHelperTrait;
    
    /**
     * @var BrokerInterface
     */
    protected $broker;
    
    /**
     * Callback to execute for each entry on the queue
     *
     * @var callable
     */
    protected $callback;
    
    /**
     * QueueInterface set of all declared ones
     *
     * @var CollectionSet
     */
    protected $queues;
    
    /**
     * Consumers who consume on the current worker
     *
     * @var ConsumersSet
     */
    protected $consumers;
    
    /**
     * @var callable
     */
    protected $setup;
    
    public function __construct(BrokerInterface $broker, $modules = array()) {
        $this->broker = $broker;
        $this->queues = new CollectionSet($broker->getQueues());
        
        $this->initModules($modules);
        $this->setConsumers();
    }
    
    public function status() {
        return array(
            'properties' => $this->props(),
            'queues'     => $this->queues->map(function(QueueInterface $queue) {
                $json = $queue->status()->json;
                return array(
                    'messages' => $json->messages,
                    'consumers' => $json->consumers,
                    'details' => array_map(function($cs){
                        return array($cs->consumer_tag, $cs->channel_details->name);
                    }, isset($json->consumer_details) ? $json->consumer_details : []),
                );
            })->toArray(),
            'consumers'  => $this->consumers->map(function(ConsumerAbstract $consumer) {
                return $consumer->tag;
            })->toArray(),
            'modules'    => $this->modules->map(function(ModuleInterface $module) {
                return get_class($module);
            })->toArray()
        );
    }
    
    /**
     * Register the set of consumers to the current worker
     *
     * @return $this
     */
    protected function setConsumers() {
        $this->consumers = new ConsumersSet();
        $this->queues->each(function(QueueInterface $queue){
            $this->broker->add($this, $queue, $this->consumers);
        });
        
        return $this;
    }
    
    /**
     * @return \WAUQueue\Contracts\Client\ConsumersSet
     */
    public function allConsumers() {
        if(!$this->consumers) $this->consumers = new ConsumersSet();
        
        return $this->consumers;
    }
    
    /**
     * @param \WAUQueue\Contracts\Client\ConsumerInterface $consumer
     *
     * @return $this
     */
    public function pushConsumer(ConsumerInterface $consumer) {
        if(!$this->consumers) $this->consumers = new ConsumersSet();
        
        $this->consumers->push($consumer);
        
        return $this;
    }
    
    /**
     * Set the prefetch size
     *
     * @param $size
     *
     * @return $this
     */
    public function prefetchSize($size) {
        return $this->setProperty('prefetch.size', intval($size));
    }
    
    /**
     * Set the prefetch count
     *
     * @param $count
     *
     * @return $this
     */
    public function prefetch($count) {
        return $this->setProperty('prefetch.count', intval($count));
    }
    
    /**
     * @param callable $callback
     *
     * @return WorkerInterface
     */
    public function setCallback(callable $callback) {
        $this->callback = $callback;
        
        return $this;
    }
    
    /**
     * @return callable
     */
    public function getCallback() {
        return $this->callback;
    }
    
    /**
     * Setup the worker, register callback to modify channel object
     *
     * @param callable $setup
     */
    public function setup(callable $setup) {
        $this->setup = $setup;
    }
    
    /**
     * Change the channel behavior/interaction with the worker
     *
     * @param ObservableInterface $channel
     */
    public function applySetup(ObservableInterface $channel) {
        if(is_callable($this->setup))
            call_user_func($this->setup, $channel);
    }
    
    /**
     * Listen to set of channel
     *
     * @param ObservableInterface $channel
     */
    public function listen(ObservableInterface $channel) {
        $channel->consume($this);
    }
    
    /**
     * @return CollectionSet
     */
    public function getQueues() {
        return $this->queues;
    }
    
    /**
     * @return CollectionSet
     */
    public function getConsumers() {
        return $this->consumers;
    }
}