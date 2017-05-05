<?php namespace WAUQueue\Contracts;


use WAUQueue\Worker;

interface ObservableInterface
{
    /**
     * @return mixed
     */
    public function channel();
    
    /**
     * @param \WAUQueue\Worker $worker the consumer client object
     *
     * @return mixed
     */
    public function consume(Worker $worker);
}