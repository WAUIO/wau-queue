<?php
namespace WAUQueue\Contracts;

/**
 * Define A strategy for the worker of the Queue
 * @author Andrianina OELIMAHEFASON
 */
interface IWorker {
    public function listen($connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0, $options = []);
}
