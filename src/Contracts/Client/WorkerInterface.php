<?php namespace WAUQueue\Contracts\Client;


use WAUQueue\Contracts\ObservableInterface;

interface WorkerInterface
{
    public function listen(ObservableInterface $channel);
    
    public function setCallback(callable $callback);
}