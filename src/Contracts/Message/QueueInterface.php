<?php namespace WAUQueue\Contracts\Message;


interface QueueInterface
{
    public function getName();
    
    public function status();
    
    public function prop($key, $defaut = null);
}