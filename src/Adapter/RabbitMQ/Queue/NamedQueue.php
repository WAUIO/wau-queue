<?php namespace WAUQueue\Adapter\RabbitMQ\Queue;


use WAUQueue\Adapter\RabbitMQ\Channel;
use WAUQueue\Adapter\RabbitMQ\Rest\Resource\Queue as QueueRest;
use WAUQueue\Contracts\Message\QueueInterface;
use WAUQueue\Helpers\PropertiesTrait;
use WAUQueue\Helpers\Utilities;

class NamedQueue implements QueueInterface
{
    use PropertiesTrait, Utilities;
    
    /**
     * @var Channel
     */
    protected $channel;
    
    protected $autoCreated = false;
    
    public $info;
    
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
        
        $this->info = QueueRest::retrieve($this->prop('__.vhost', ''), $this->prop('name'));
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
    
    public function status() {
        return QueueRest::retrieve($this->prop('__.vhost', ''), $this->prop('name'));
    }
    
    public function getName() {
        return $this->prop('name', '');
    }
}