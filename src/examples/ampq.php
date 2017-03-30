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
        'durable' => false,
        'auto_delete' => false,
        'name' => 'email'
    ),
];

