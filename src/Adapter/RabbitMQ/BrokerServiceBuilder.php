<?php namespace WAUQueue\Adapter\RabbitMQ;


use PhpAmqpLib\Message\AMQPMessage;
use WAUQueue\Adapter\RabbitMQ\Exchange\BasicExchange;
use WAUQueue\Contracts\ClosableInterface;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Contracts\Message\BrokerAbstract;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\MessageInterface;
use WAUQueue\Contracts\ObservableInterface;
use WAUQueue\Exception\ConcurrentQueueError;
use WAUQueue\Worker;

class BrokerServiceBuilder extends BrokerAbstract implements BrokerInterface, ObservableInterface, ClosableInterface
{
    /**
     * @var ObservableInterface
     */
    protected $channel;
    
    /**
     * @var Exchange\BasicExchange
     */
    protected $exchange;
    
    /**
     * @var QueueInterface
     */
    protected $queue;
    
    /**
     * Set of QueueInterface, as associative array
     *  {
     *      "queue-1" => QueueInterface,
     *      "queue-2" => QueueInterface,
     *      ...
     *  }
     *
     * @var array
     */
    protected $queues;
    
    /**
     * @inheritdoc
     */
    public function pull(MessageInterface $message) {
        // build the concrete message
        $concreteMessage = new AMQPMessage(
            $message->raw(),
            $message->getHeaders()
        );
        
        $config = $message->getConfig();
        
        $exchange = is_null($this->exchange) ? '' : $this->exchange->prop('name');
        
        // send the message to the broker, precisely to exchange
        $this->channel()->get()->basic_publish($concreteMessage, $exchange,
            $this->array_get($config, 'routing'),
            $this->array_get($config, 'mandatory', false),
            $this->array_get($config, 'immediate', false),
            $this->array_get($config, 'ticket')
        );
    }
    
    /**
     * @param BasicExchange $exchange
     *
     * @return BasicExchange
     */
    public function setExchange(BasicExchange $exchange) {
        $this->exchange = $exchange;
        
        return $this->exchange;
    }
    
    /**
     * Set a queue
     *
     * @param QueueInterface $queue
     *
     * @return QueueInterface
     */
    public function setQueue(QueueInterface $queue) {
        if(!is_null($this->queue) && !$this->option('accept-multi-queues')) {
            // todo: delete the current one and replace it with this one
            // for new just let's throw an exception
            throw new ConcurrentQueueError("A queue is already set for this worker, name = \"{$this->queue->getName()}\"");
        }
        
        $this->queue = $queue;
        $this->queues[$queue->getName()] = $queue;
        
        return $queue;
    }
    
    /**
     * @param BasicExchange  $exchange
     * @param QueueInterface $queue
     * @param string         $key
     * @param array          $bindingOptions
     *
     * @return $this
     */
    public function bind(Exchange\BasicExchange $exchange, QueueInterface $queue, $key = '', $bindingOptions = array()) {
        $this->setExchange($exchange);
        $this->setQueue($queue);
        
        $bindingOptions['key'] = $key;
        
        $exchange->bind($queue,
            $this->array_get($bindingOptions, 'key', ''),
            $this->array_get($bindingOptions, 'nowait', false),
            $this->array_get($bindingOptions, 'arguments'),
            $this->array_get($bindingOptions, 'ticket')
        );
        
        return $this;
    }
    
    /**
     * @inheritdoc
     *
     * @return QueueInterface
     */
    public function getQueue($name = null) {
        if(is_null($name)) {
            $queueSet = end($this->queues);
        } else {
            $queueSet = $this->array_get($this->queues, $name, null);
        }
        
        return !empty($queueSet) ? $queueSet['object'] : $this->setQueue(new Queue\RandomQueue($this->channel(), []));
    }
    
    /**
     * Get all queues declared in the current broker
     *
     * @return array
     */
    public function getQueues() {
        return $this->queues;
    }
    
    /**
     * Get the instanciated Observable Channel object
     *
     * @return Channel
     */
    public function channel() {
        if(is_null($this->channel)) {
            $this->channel = new Channel($this->connect()->connect([]));
        }
        
        return $this->channel;
    }
    
    /**
     * @inheritdoc
     */
    public function consume(Worker $worker) {
        $this->channel()->consume($worker);
    }
    
    /**
     * Close the whole stream abstract, both Channel and the Connection Stream
     */
    public function close() {
        $this->channel()->close();
    }
}