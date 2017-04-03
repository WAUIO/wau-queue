<?php
/**
 * Created by PhpStorm.
 * User: Iz
 * Date: 04/04/2017
 * Time: 15:41
 */

namespace WAUQueue\Adapter\RabbitMQ\Queue;


use WAUQueue\Adapter\RabbitMQ\Channel;

class AutoQueue extends NamedQueue
{
    
    protected $autoCreated = true;
    
    public function __construct(Channel $channel, array $config) {
        parent::__construct($channel, $config, '');
    }
}