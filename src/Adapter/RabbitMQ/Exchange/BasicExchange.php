<?php namespace WAUQueue\Adapter\RabbitMQ\Exchange;


use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Helpers\PropertiesTrait;

class BasicExchange
{
    use PropertiesTrait;
    
    /**
     * @var \WAUQueue\Adapter\RabbitMQ\Channel
     */
    protected $channel;
    
    public function __construct($channel, array $config, $type) {
        $this->channel    = $channel;
        $this->properties = $config;
        $this->setProperty('type', $type);
        
        $this->declareIt();
    }
    
    /**
     * Declare the Exchange object
     */
    public function declareIt() {
        $this->channel->get()->exchange_declare(
            $this->prop('name'),
            $this->prop('type'),
            $this->prop('passive', false),
            $this->prop('durable', false),
            $this->prop('auto_delete', true),
            $this->prop('internal', false),
            $this->prop('nowait', false),
            $this->prop('arguments'),
            $this->prop('ticket')
        );
    }
    
    /**
     * Bind a routing key to a specific queue
     *
     * @param \WAUQueue\Contracts\Message\QueueInterface $queue
     * @param string                                     $routingKey
     * @param bool                                       $nowait
     * @param null                                       $arguments
     * @param null                                       $ticket
     */
    public function bind(QueueInterface $queue, $routingKey = '', $nowait = false, $arguments = null, $ticket = null) {
        if(is_array($routingKey)) {
            foreach ($routingKey as $key) {
                $this->bind($queue, $key, $nowait, $arguments, $ticket);
            }
        } elseif (is_string($routingKey)) {
            $this->channel->get()->queue_bind($queue->getName(), $this->prop('name'),
                $routingKey, $nowait, $arguments, $ticket
            );
        }
    }
}