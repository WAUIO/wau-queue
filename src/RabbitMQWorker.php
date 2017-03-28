<?php
namespace WAUQueue;

use WAUQueue\Contracts\IWorker;
use WAUQueue\Connectors\RabbitMQConnnector;

class RabbitMQWorker implements IWorker {
    
    private $connection;
    
    public function __construct() {
        $this->connection = RabbitMQConnnector::$queue;
    }
    
    /**
	 * Listen to the given queue connection.
	 *
	 * @param  string  $connectionName
	 * @param  string  $queue
	 * @param  string  $delay
	 * @param  string  $memory
	 * @param  int     $timeout
	 * @return void
	 */
	public function listen($connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0)
	{
        $this->connection->initProcess();
        while(true) {
            sleep(1);
            
            $this->connection->pop($queue);
//            $this->connection->getChannel()->wait();
        }
	}
    
    public function runProcess() {
        
    }
    
    public function makeProcess() {
        
    }
}
