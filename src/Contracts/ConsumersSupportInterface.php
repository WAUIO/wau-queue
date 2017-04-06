<?php namespace WAUQueue\Contracts;


use WAUQueue\Contracts\Client\ConsumerInterface;

interface ConsumersSupportInterface
{
    /**
     * @return mixed
     */
    public function allConsumers();
    
    /**
     * @param ConsumerInterface $consumer
     *
     * @return mixed
     */
    public function pushConsumer(ConsumerInterface $consumer);
}