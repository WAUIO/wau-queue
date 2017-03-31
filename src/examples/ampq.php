<?php

return [
    
    'host' => 'localhost', 
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest',
    'vhost' => '/',
    'queue' => 'test',
    'queue_params' => array(
        'priority' => 9,
        'passive' => false,
        'durable' => false,
        'exclusive' => false,
        'auto_delete' => false
    ),
    
    'exchange_params' => array(
        'type' => 'direct',
        'passive' => false,
        'durable' => true, // Do not lose message even if the server crashed
        'auto_delete' => false,
    ),
];

