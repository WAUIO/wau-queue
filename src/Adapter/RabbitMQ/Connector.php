<?php namespace WAUQueue\Adapter\RabbitMQ;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use WAUQueue\Connectors\ConnectorInterface;
use WAUQueue\Contracts\ClosableInterface;
use WAUQueue\Exception\ConnectionError;
use WAUQueue\Helpers\PropertiesTrait;

/**
 * Class Connector
 *
 * @package WAUQueue\Adapter\RabbitMQ
 */
class Connector implements ConnectorInterface, ClosableInterface
{
    use PropertiesTrait;
    
    /**
     * Connection Stream
     *
     * @var AMQPStreamConnection
     */
    protected $stream;
    
    /**
     * Check if we ar connected or not
     *
     * @var bool
     */
    protected $connected = false;
    
    public function __construct(array $config = array()) {
        $this->properties = $config;
    }
    
    /**
     * Get the stream as singleton in the stream property object
     *
     * @inheritdoc
     */
    public function connect(array $config = array()) {
        // new set of config, merged with the dataset on instanciation
        $this->properties = array_merge($this->properties, $config);
        
        if (is_null($this->stream)) {
            try {
                $this->stream = new AMQPStreamConnection(
                    $this->prop('host'),
                    $this->prop('port'),
                    $this->prop('user'),
                    $this->prop('password'),
                    $this->prop('vhost', '/'),
                    $this->prop('insist', false),
                    $this->prop('method', 'AMQPLAIN'),
                    $this->prop('response'),
                    $this->prop('locale', 'en_US'),
                    $this->prop('connection_timeout', 3),
                    $this->prop('rw_timeout', 3),
                    $this->prop('context'),
                    $this->prop('keep_alive', false),
                    $this->prop('heartbeat', 0)
                );
                
                $this->connected = true;
            } catch (\Exception $e) {
                throw new ConnectionError('Error while connecting to the remote AMQP service', 500, $e);
            }
        }
        
        return $this->stream;
    }
    
    /**
     * Get Ready state, from Connector interface
     *
     * @return bool
     */
    public function ready() {
        return $this->connected;
    }
    
    /**
     * Close the connection
     */
    public function close() {
        if($this->connected) {
            $this->stream->close();
        }
    }
}