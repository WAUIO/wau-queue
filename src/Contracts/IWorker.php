<?php
namespace WAUQueue\Contracts;

/**
 * Define A strategy for the worker of the Queue
 *
 * @deprecated
 * (No more in use on 2.x but should consider the parameters to implement it in another way)
 *
 * @author Andrianina OELIMAHEFASON
 */
interface IWorker {
    /**
     * Listen to the given queue connection.
     * 
     * @param string $queue
     * @param array $options
     * @param integer $delay
     * @param integer $memory
     * @param integer $sleep
     * @param integer $maxTries
     */
    public function listen($queue = null, $options = array(), $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0 );
}
