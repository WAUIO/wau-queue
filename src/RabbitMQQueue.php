<?php
namespace WAUQueue;

use WAUQueue\Queue;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Description of RabbitMQQueue
 *
 * @author Andrianina OELIMAHEFASON
 */
class RabbitMQQueue extends Queue{
    
    use InteractsWithTime;
    /**
     * Used for retry logic, to set the retries on the message metadata instead of the message body.
     */
    const ATTEMPT_COUNT_HEADERS_KEY = 'attempts_count';
    
    protected $connection;
    
    protected $channel;
    
    protected $declareExchange;
    
    protected $declaredExchanges = [];
    
    protected $declareBindQueue;
    
    protected $sleepOnError;
    
    protected $declaredQueues = [];
    
    protected $defaultQueue;
    
    protected $configQueue;
    
    protected $configExchange;
    
    /**
     * @var int
     */
    private $retryAfter;
    
    /**
     * @param AMQPStreamConnection $amqpConnection
     * @param array                $config
     */
    public function __construct(AMQPStreamConnection $amqpConnection, $config)
    {
        $this->connection = $amqpConnection;
        $this->defaultQueue = $config['queue'];
        $this->configQueue = $config['queue_params'];
        $this->configExchange = isset($config['exchange_params']) ? $config['exchange_params'] : [];
        $this->declareExchange = isset($config['exchange_declare']) ? $config['exchange_declare'] : false;
        $this->declareBindQueue = isset($config['queue_declare_bind']) ? $config['queue_declare_bind'] : false;
        $this->sleepOnError = isset($config['sleep_on_error']) ? $config['sleep_on_error'] : 5;
        $this->channel = $this->getChannel();
    }
    
    private function setAttribute($attribute) {
        return isset($attribute) ? $attribute : false;
    }
    /**
     * Push a new job onto the queue.
     *
     * @param string $job
     * @param mixed  $data
     * @param string $queue
     *
     * @return bool
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, array());
    }
    
    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param mixed $data
     * @param string $queue
     * @param array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, $options = array())
    {
        try {
            $queue = $this->getQueueName($queue);
            $this->declareQueue($queue);
            if (isset($options['delay']) && $options['delay'] > 0) {
                list($queue, $exchange) = $this->declareDelayedQueue($queue, $options['delay']);
            } else {
                list($queue, $exchange) = $this->declareQueue($queue);
            }
            
            $this->send($payload, $queue, $exchange);
//            $headers = [
//                'Content-Type'  => 'application/json',
//                'delivery_mode' => 2,
//            ];
//            if (isset($this->retryAfter) === true) {
//                $headers['application_headers'] = [self::ATTEMPT_COUNT_HEADERS_KEY => ['I', $this->retryAfter]];
//            }
//            
//            $this->addPriority($options, $headers);
//            // push job to a queue
//            $message = new AMQPMessage($payload, $headers);
//            // push task to a queue
//            $this->channel->basic_publish($message, $exchange, $queue);
            
            $this->channel->close();
            $this->connection->close();
        } catch (\Exception $exception) {
            // @Todo : set log error to file or something else;
            var_dump($exception->getMessage());
        }
    }
    
    private function send($data, $queue = '', $exchange = '') {
        $priority = rand(1,9);
        $msg = new AMQPMessage($data, array(
            'delivery_mode' => 2,
            'priority' => $priority,
            'timestamp' => time(),
    //        'expiration' => strval(1000 * (strtotime('+1 day midnight') - time() - 1))
        ));

        $this->channel->basic_publish($msg, $exchange, $queue);
    }
    
    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueueName($queue);
        try {
            // declare queue if not exists
            $this->declareQueue($queue);
            // get envelope
            $message = $this->channel->basic_get($queue);
            if ($message instanceof AMQPMessage) {
//                return new RabbitMQJob($this, $this->channel, $queue, $message);
            }
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
        }
    }
    
    /**
     * @param string $queue
     *
     * @return string
     */
    private function getQueueName($queue)
    {
        return $queue ?: $this->defaultQueue;
    }
    
    /**
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->connection->channel();
    }
    
    /**
     * @param $name
     *
     * @return array
     */
    private function declareQueue($name)
    {
        $name = $this->getQueueName($name);
        $exchange = $this->configExchange['name'] ?: $name;
        if ($this->declareExchange && !in_array($exchange, $this->declaredExchanges)) {
            // declare exchange
            $this->channel->exchange_declare(
                $exchange,
                $this->configExchange['type'],
                $this->configExchange['passive'],
                $this->configExchange['durable'],
                $this->configExchange['auto_delete']
            );
            $this->declaredExchanges[] = $exchange;
        }
        if ($this->declareBindQueue && !in_array($name, $this->declaredQueues)) {
            $params = $this->setPriorityParams($this->configQueue, $this->configQueue);
            // declare queue
            $this->channel->queue_declare(
                $name,
                $this->configQueue['passive'],
                $this->configQueue['durable'],
                $this->configQueue['exclusive'],
                $this->configQueue['auto_delete'],
                false,
                new AMQPTable($params)
            );
            
            // bind queue to the exchange
            $this->channel->queue_bind($name, $exchange, $name);
            $this->declaredQueues[] = $name;
        }
        return [$name, $exchange];
    }
    
    /**
     * @param string       $destination
     * @param DateTime|int $delay
     *
     * @return string
     */
    private function declareDelayedQueue($destination, $delay)
    {
        $delay = $this->secondsUntil($delay);
        $destination = $this->getQueueName($destination);
        $destinationExchange = $this->configExchange['name'] ?: $destination;
        $name = $this->getQueueName($destination).'_deferred_'.$delay;
        $exchange = $this->configExchange['name'] ?: $destination;
        // declare exchange
        if (!in_array($exchange, $this->declaredExchanges)) {
            $this->channel->exchange_declare(
                $exchange,
                $this->configExchange['type'],
                $this->configExchange['passive'],
                $this->configExchange['durable'],
                $this->configExchange['auto_delete']
            );
        }
        // declare queue
        if (!in_array($name, $this->declaredQueues)) {
            $this->channel->queue_declare(
                $name,
                $this->configQueue['passive'],
                $this->configQueue['durable'],
                $this->configQueue['exclusive'],
                $this->configQueue['auto_delete'],
                false,
                new AMQPTable([
                    'x-dead-letter-exchange'    => $destinationExchange,
                    'x-dead-letter-routing-key' => $destination,
                    'x-message-ttl'             => $delay * 1000,
                ])
            );
        }
        // bind queue to the exchange
        $this->channel->queue_bind($name, $exchange, $name);
        return [$name, $exchange];
    }
    
    public function addPriority($options, &$headers) {
        if (isset($options['priority'])) {
            $headers['priority'] = (int) $options['priority'];
        }
    }
    
    private function setPriorityParams($params = [] , $configs) {
        if (isset($configs['priority'])) {
            $params['x-max-pririty'] = $configs['priority'];
        }
        return $params;
    }
}
