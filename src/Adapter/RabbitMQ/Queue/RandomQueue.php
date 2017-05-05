<?php namespace WAUQueue\Adapter\RabbitMQ\Queue;


use WAUQueue\Adapter\RabbitMQ\Channel;

class RandomQueue extends NamedQueue
{
    
    protected $autoCreated = true;
    
    public function __construct(Channel $channel, array $config) {
        parent::__construct($channel, $config, '');
        //parent::__construct($channel, $config, $this->array_get($config, '__.prefix', '') . md5(uniqid() . microtime(true)));
    }
}