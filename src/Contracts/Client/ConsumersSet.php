<?php namespace WAUQueue\Contracts\Client;


use WAUQueue\Contracts\ConsumersSupportInterface;
use WAUQueue\Helpers\CollectionSet;

class ConsumersSet extends CollectionSet implements ConsumersSupportInterface
{
    public function allConsumers() {
        return $this;
    }
    
    public function pushConsumer(ConsumerInterface $consumer){
        $this->push($consumer);
    }
}