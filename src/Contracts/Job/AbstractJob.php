<?php namespace WAUQueue\Contracts\Job;


use WAUQueue\Contracts\Client\WorkerInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Helpers\BashOutput\BashOutputAbilityTrait;

/**
 * Description of JobAbstract
 *
 * @author Andrianina OELIMAHEFASON
 */
abstract class AbstractJob implements InterfaceJob
{
    use BashOutputAbilityTrait;
    
    /**
     * The current worker
     *
     * @var WorkerInterface
     */
    protected $worker;
    
    /**
     * Work-on Channel
     *
     * @var ObservableInterface
     */
    protected $channel;
    
    /**
     * Listened queue
     *
     * @var QueueInterface
     */
    protected $queue;
    
    public function __construct(WorkerInterface $worker, ObservableInterface $channel, QueueInterface $queue) {
        $this->worker  = $worker;
        $this->channel = $channel;
        $this->queue   = $queue;
        
        $this->registerDefaultStyles();
    }
    
    /**
     * @param mixed|\PhpAmqpLib\Message\AMQPMessage $message
     *
     * @return mixed
     */
    abstract public function fire($message);
}

