<?php namespace WAUQueue\Contracts\Message;


use WAUQueue\Contracts\Client\ConsumerInterface;
use WAUQueue\Contracts\Client\WorkerInterface;

interface BrokerInterface
{
    /**
     * Recieve a message from a client per example
     *
     * @param \WAUQueue\Contracts\Message\MessageInterface $message
     *
     * @return mixed
     */
    public function pull(MessageInterface $message);
    
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
     * @param \WAUQueue\Contracts\Client\WorkerInterface $worker
     * @param \WAUQueue\Contracts\Message\QueueInterface $queue
     *
     * @return mixed
     */
    public function add(WorkerInterface $worker, QueueInterface $queue);
}