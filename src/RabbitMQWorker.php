<?php
namespace WAUQueue;

use WAUQueue\Contracts\IWorker;
use WAUQueue\Connectors\RabbitMQWorkerConnector as Connector;
use WAUQueue\RabbitMQQueue;
use League\Pipeline\Pipeline;
use WAUQueue\Contracts\AbstractStagePipeline;
use WAUQueue\Contracts\Job\JobMaker;

class RabbitMQWorker implements IWorker {
    
    use Helpers\PayloadTrait;
    
    private $connection;
    
    private $queue;
    
    /**
     * Item to chech for the pipeline
     * @var array
     */
    private $handlers;
    
    public function __construct($configs, $handlers = array()) {
        $con = new Connector();
        $con->connect($configs);
        $this->connection = $con->connection();
        $this->queue = new RabbitMQQueue($this->connection, $configs);
        $this->handlers = $handlers;
    }
    
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
    public function listen($queue = null, $options = array(), $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0 )
	{
        try  {
            $this->queue->declareQueue($queue);
            $this->bindQueue($queue, $options);
            $this->lockMessage();
            $channel = $this->connection->channel();
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

            while(count($channel->callbacks)) {
                $pipeline = $this->attachPipeline();
                if($pipeline->process(true)) {
                    $channel->wait();
                }
            }
            $channel->close();
            $this->connection->close();
        } catch (Exception $e) {
            //reconnect on exception
            echo "Exception handled, reconnecting...\nDetail:\n".$e.PHP_EOL;
            if ($this->connection != null) {
                try {
                    $this->connection->close();
                } catch (Exception $e1) {}
            }
            sleep(5);
        }
	}
    
    /**
    * don't dispatch a new message to a worker until it has processed and 
    * acknowledged the previous one. Instead, it will dispatch it to the 
    * next worker that is not still busy.
    */
    private function lockMessage() {
        $channel =  $this->connection->channel();
        $channel->basic_qos(
            null,   #prefetch size - prefetch window size in octets, null meaning "no specific limit"
            1,      #prefetch count - prefetch window in terms of whole messages
            null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );
    }
    
    /**
     * Callback for the channel 
     *
     * @return function
     */
    public function runProcess() {
        return function($message) {
            $payload = $this->unserializePayload($message->body);
            $jobMaker = (new JobMaker())->makeJob($payload->job);
            if (!is_null($jobMaker)) {
                if($jobMaker->fire($payload->job, $payload->data)) {
                    // Ack the message to the queue (delete it)
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                }
            } else {
                // @todo: requeue the message
                echo "/!\ Item not consumed. There are no Job to consume the item.".PHP_EOL;
            }
        };
    }
    
    /**
     * Bind queue
     *
     * @param string $queue
     * @param array $options
     */
    public function bindQueue($queue, $options) {
        if (isset($options['binding_queue_route']) && isset($options['exchange'])) {
            $this->connection->channel()->queue_bind($queue, $options['exchange'], $options['binding_queue']);
        }
    }
    
    /**
     * Attach the handlers to the pipeline
     * 
     * @return Pipeline;
     */
    private function attachPipeline() {
        $pipeline = new Pipeline();
        foreach ($this->handlers as $stagePipeline) {
            if ($pipeline instanceof AbstractStagePipeline ) {
                $pipeline = $pipeline->pipe($stagePipeline);
            }
        }
        return $pipeline;
    }
}
