<?php namespace WAUQueue\Contracts\Message;


interface BrokerInterface
{
    /**
     * Recieve a message from a client per example
     *
     * @param \WAUQueue\Contracts\Message\MessageInterface $message
     *
     * @return mixed
     */
    public function receive(MessageInterface $message);
    
    /**
     * Get a specific queue object in the broker
     *
     * @param null $name
     *
     * @return null|\WAUQueue\Contracts\Message\QueueInterface
     */
    public function getQueue($name = null);
}