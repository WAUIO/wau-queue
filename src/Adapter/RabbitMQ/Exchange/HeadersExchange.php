<?php namespace WAUQueue\Adapter\RabbitMQ\Exchange;


use WAUQueue\Adapter\RabbitMQ\Channel;

class HeadersExchange extends BasicExchange
{
    public function __construct(Channel $channel, array $config = array()) {
        parent::__construct($channel, $config, 'headers');
    }
}