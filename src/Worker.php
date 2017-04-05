<?php namespace WAUQueue;


use WAUQueue\Contracts\Client\WorkerInterface;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Helpers\PropertiesTrait;

/**
 * Class Worker
 *
 * Basic Worker that should be compatibe with all type of Broker
 *
 * @package WAUQueue
 */
class Worker implements WorkerInterface
{
    use PropertiesTrait;
    
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
     * @var CollectionSet
     */
    protected $consumers;
    
    /**
     * @var callable
     */
    protected $setup;
    
    public function __construct(BrokerInterface $broker) {
        $this->broker = $broker;
        $this->queues = new CollectionSet($broker->getQueues());
        
        $this->setConsumers();
    }
    
    /**
     * Register the set of consumers to the current worker
     *
     * @return $this
     */
    protected function setConsumers() {
        $this->consumers = new CollectionSet();
        $this->queues->each(function(QueueInterface $queue){
            $this->consumers->push($this->broker->add($this, $queue));
        });
        
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