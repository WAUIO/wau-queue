<?php namespace WAUQueue\Contracts\Message;


interface QueueInterface
{
    public function getName();
    
    public function getInfo();
    
    public function prop($key, $defaut = null);
}