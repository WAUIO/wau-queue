<?php namespace WAUQueue\Contracts\Client;


interface ConsumerInterface
{
    /**
     * Process consuming
     *
     * @param array $strategy
     *
     * @return mixed
     */
    public function consume(array $strategy = array());
    
    /**
     * Set the callback for the consumer
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function setCallback(callable $callback);
    
    /**
     * Set the job class name
     *
     * @param $jobName
     *
     * @return string
     */
    public function setJob($jobName);
}