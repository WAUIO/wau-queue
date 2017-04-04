<?php namespace WAUQueue\Contracts\Message;


class MessageAbstract
{
    protected $payload;
    
    protected $config = array();
    
    protected $headers = array();
}