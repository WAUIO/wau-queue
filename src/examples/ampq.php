<?php

return [
    
    'host' => 'localhost', 
    'port' => 5672,
    'login' => 'guest',
    'password' => 'guest',
    'vhost' => '/',
    'queue' => 'test',
//    'exchange_params' => array(
//        'type' => 'direct',
//        'passive' => false,
//        'durable' => false,
//        'auto_delete' => false,
//        'name' => 'sms'
//    ),
    'queue_params' => array(
        'passive' => false,
        'durable' => false,
        'exclusive' => false,
        'auto_delete' => false
    ),
    
    'exchange_declare' => $exchange,
    'queue_declare_bind'=> 'sms',
];

