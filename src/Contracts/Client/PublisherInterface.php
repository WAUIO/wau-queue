<?php namespace WAUQueue\Contracts\Client;


use WAUQueue\Contracts\Message\MessageInterface;

interface PublisherInterface
{
    public function publish(MessageInterface $message);
}