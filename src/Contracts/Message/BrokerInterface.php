<?php namespace WAUQueue\Contracts\Message;


use WAUQueue\Contracts\Client\ConsumerInterface;
use WAUQueue\Contracts\Client\ConsumersSet;
use WAUQueue\Contracts\Client\WorkerInterface;
use WAUQueue\Contracts\ConsumersSupportInterface;

interface BrokerInterface
{
    /**
     * Recieve a message from a client per example
     *
     * @param \WAUQueue\Contracts\Message\MessageInterface $message
     * @param string $exchangeName
     *
     * @return mixed
     */
    public function pull(MessageInterface $message, $exchangeName = "");
    
    /**
     * Get a specific queue object in the broker
     *
     * @param null $name
     *
     * @return null|\WAUQueue\Contracts\Message\QueueInterface
     */
    public function getQueue($name = null);
    
    /**
     * Get all declared queues on one open broker
     *
     * @return array
     */
    public function getQueues();
    
    /**
     * Add a new consumer on a worker
     *
     * @param \WAUQueue\Contracts\Client\WorkerInterface    $worker
     * @param \WAUQueue\Contracts\Message\QueueInterface    $queue
     * @param \WAUQueue\Contracts\ConsumersSupportInterface $support
     *
     * @return mixed
     */
    public function add(WorkerInterface $worker, QueueInterface $queue, ConsumersSupportInterface &$support);
    
    /**
     * Remove a consumer from channel
     *
     * @param string $consumerTag
     * @param bool   $nowait
     *
     * @return mixed
     */
    public function remove($consumerTag, $nowait = false);
    
    /**
     * Get object property
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function prop($key, $default = null);
}