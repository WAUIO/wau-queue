<?php namespace WAUQueue\Adapter\RabbitMQ\Queue;


use WAUQueue\Adapter\RabbitMQ\Channel;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Helpers\PropertiesTrait;

class NamedQueue implements QueueInterface
{
    use PropertiesTrait;
    
    /**
     * @var Channel
     */
    protected $channel;
    
    protected $autoCreated = false;
    
    public function __construct(Channel $channel, array $config, $name = '') {
        $this->channel    = $channel;
        $this->properties = $config;
        $this->setProperty('name', $name);
        
        $this->create();
    }
    
    /**
     * Declare the queue object in the broker
     */
    protected function create() {
        if($this->prop('name', '') == '' && $this->autoCreated) {
            list($newQueue, ,) = $this->createWithConfig();
            
            // reset the name from the retrieved one
            $this->setProperty('name', $newQueue);
        } else {
            $this->createWithConfig();
        }
    }
    
    /**
     * Concrete scipt to declare the queue
     *
     * @return mixed|null
     */
    protected function createWithConfig() {
        return $this->channel->get()->queue_declare(
            $this->prop('name', ''),
            $this->prop('passive', false),
            $this->prop('durable', false),
            $this->prop('exclusive', false),
            $this->prop('auto_delete', true),
            $this->prop('nowait', false),
            $this->prop('arguments'),
            $this->prop('ticket')
        );
    }
    
    public function getName() {
        return $this->prop('name', '');
    }
}