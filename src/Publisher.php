<?php namespace WAUQueue;


use WAUQueue\Contracts\Client\PublisherInterface;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Contracts\Message\MessageInterface;

/**
 * Class Publisher
 *
 * @package WAUQueue
 */
class Publisher implements PublisherInterface
{
    /**
     * Abstraction of the server
     *
     * @var BrokerInterface
     */
    protected $server;
    
    public function __construct(BrokerInterface $server) {
        $this->server = $server;
    }
    
    /**
     * Publish the message packet to the broker
     *
     * @param MessageInterface $message
     */
    public function publish(MessageInterface $message) {
        $this->server->pull($message);
    }
}