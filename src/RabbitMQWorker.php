<?php
namespace WAUQueue;

use WAUQueue\Contracts\IWorker;
use WAUQueue\Connectors\RabbitMQWorkerConnector as Connector;

class RabbitMQWorker implements IWorker {
    
    private $connection;
    
    public function __construct($configs) {
        $con = new Connector();
        $con->connect($configs);
        $this->connection = $con->connection();
    }
    
    /**
	 * Listen to the given queue connection.
	 *
	 * @param  string  $queue
	 * @param  string  $delay
	 * @param  string  $memory
	 * @param  int     $timeout
	 * @return void
	 */
	public function listen($queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0, $options = [])
	{
        $channel = $this->connection->channel();
        
        if (isset($options['binding_queue']) && isset($options['exchange'])) {
            $channel->queue_bind($queue, $options['exchange'], $options['binding_queue']);
        }
        
        $callback = function($message) {
            echo "Sent  : {$message->body} ".PHP_EOL;
            // @todo : Make factory method to create a new Job 
            // and fire Job::fire() to do the Job.
            // Implement WAUQueue\Contract\Job for Different Job
//            $provider = (new MailProducer())->makeSender('sms');
//            $provider->fire();
            
            // Ack the message to the queue (delete it)
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
        $channel->basic_consume(
                $queue, 
                '', 
                false, 
                false, 
                false, 
                false, 
                $this->runProcess(), 
                null, 
                array('x-priority' => array('I', 9))
                );
        while(true) {
            $channel->wait();
        }
        $channel->closse();
        $this->connection->close();
	}
    
    public function runProcess() {
        return function($message) {
            echo "Sent  : {$message->body} ".PHP_EOL;
            // @todo : Make factory method to create a new Job 
            // and fire Job::fire() to do the Job.
            // Implement WAUQueue\Contract\Job for Different Job
//            $provider = (new MailProducer())->makeSender('sms');
//            $provider->fire();
            
            // Ack the message to the queue (delete it)
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
    }
    
    public function makeProcess() {
        
    }
}
