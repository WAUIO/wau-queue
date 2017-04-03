<?php namespace WAUQueue;


use WAUQueue\Contracts\Client\WorkerInterface;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
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
     * @var QueueInterface
     */
    protected $queue;
    
    /**
     * @var callable
     */
    protected $behavior;
    
    public function __construct(BrokerInterface $broker) {
        $this->broker = $broker;
        $this->queue  = $broker->getQueue();
    }
    
    /**
     * Callback to execute for each entry on the queue
     *
     * @var callable
     */
    protected $callback;
    
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
    public function getCallback(){
        return $this->callback;
    }
    
    /**
     * @param callable $behavior
     */
    public function setBehavior(callable $behavior) {
        $this->behavior = $behavior;
    }
    
    /**
     * Change the channel behavior/interaction with the worker
     *
     * @param ObservableInterface $channel
     */
    public function behave(ObservableInterface $channel) {
        call_user_func($this->behavior, $channel);
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
     * @return null|QueueInterface
     */
    public function getQueue(){
        return $this->queue;
    }
}