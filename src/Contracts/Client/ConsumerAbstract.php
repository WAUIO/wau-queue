<?php namespace WAUQueue\Contracts\Client;


use WAUQueue\Contracts\Job\InterfaceJob;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Exception\LogicError;
use WAUQueue\Helpers\PropertiesTrait;

/**
 * Class ConsumerAbstract
 *
 * @package WAUQueue\Adapter\RabbitMQ
 */
abstract class ConsumerAbstract implements ConsumerInterface
{
    use PropertiesTrait;
    
    /**
     * @var WorkerInterface
     */
    protected $worker;
    
    /**
     * @var ObservableInterface
     */
    protected $channel;
    
    /**
     * Callback to execute while consuming the message
     *
     * @var callable
     */
    protected $callback;
    
    /**
     * @var QueueInterface
     */
    protected $queue;
    
    /**
     * Consumer tag
     *
     * @var String
     */
    protected $tag;
    
    /**
     * Name of JobInterface to run
     *
     * @var String
     */
    protected $jobName;
    
    public function __construct(WorkerInterface $worker, ObservableInterface $channel, array $strategy = array()) {
        $this->worker     = $worker;
        $this->channel    = $channel;
        $this->properties = $strategy;
    }
    
    /**
     * Set a consumption strategy,
     * It contains all configto run while executing basic_consume method
     *
     * @param array $strategy
     *
     * @return $this
     */
    public function setStrategy(array $strategy) {
        $this->properties = array_merge($this->properties, $strategy);
        
        return $this;
    }
    
    /**
     * Listen to a declared queue
     *
     * @param QueueInterface $queue
     *
     * @return $this
     */
    public function listenTo(QueueInterface $queue) {
        $this->queue = $queue;
        
        return $this;
    }
    
    /**
     * Public mutator for the callback property
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setCallback(callable $callback) {
        $this->callback = $callback;
        
        return $this;
    }
    
    /**
     * Set the job class name
     *
     * @param $jobName
     *
     * @return $this
     */
    public function setJob($jobName) {
        $this->jobName = $jobName;
        
        return $this;
    }
    
    /**
     * Resolve the job to run for the current consumer
     *
     * @return $this
     */
    protected function resolveJob() {
        if($this->jobName && class_exists($this->jobName)) {
            $job = new $this->jobName($this->worker, $this->channel, $this->queue);
            
            if(!($job instanceof InterfaceJob)){
                throw new LogicError("Invalid job. [{$this->jobName}] should implement InterfaceJob to be valid");
            }
            
            $this->setCallback(array($job, 'fire'));
        }
        
        return $this;
    }
    
    /**
     * Abstract method to implement for each Consumer type
     *
     * @param array $strategy
     *
     * @return mixed
     */
    protected abstract function swallow(array $strategy = array());
    
    /**
     * Prepare and send consumption job signal
     *
     * @param array $strategy
     *
     * @return mixed
     */
    public function consume(array $strategy = array()) {
        // try to find a callback on the queue object first
        if(!$this->callback) {
            $callback = $this->queue->prop('__job', $this->prop('__job'));
            if (is_callable($callback)) {
                $this->setCallback($callback);
            } elseif (is_string($callback)) {
                $this->setJob($callback);
            }
        }
    
        // patch the default strategy implemented and force setup callback
        $this->setStrategy($strategy)->resolveJob();
        
        // concrete consumption
        $this->swallow($strategy);
        
        return $this;
    }
}