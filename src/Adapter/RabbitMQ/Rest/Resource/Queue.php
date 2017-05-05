<?php namespace WAUQueue\Adapter\RabbitMQ\Rest\Resource;


use WAUQueue\Adapter\RabbitMQ\Rest\RabbitMQRest;

class Queue extends RabbitMQRest
{
    public static function all() {
        return static::get("api/queues");
    }
    
    public static function onVhost($vhost) {
        return static::get("api/queues/{$vhost}");
    }
    
    public static function retrieve($vhost, $name) {
        return static::get("api/queues/{$vhost}/{$name}");
    }
}