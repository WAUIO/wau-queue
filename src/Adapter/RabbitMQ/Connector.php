<?php namespace WAUQueue\Adapter\RabbitMQ;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use WAUQueue\Adapter\RabbitMQ\Rest\RabbitMQRest;
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
        // new set of config, merged with the dataset on instantiation
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
                    $this->prop('response', null),
                    $this->prop('locale', 'en_US'),
                    $this->prop('connection_timeout', 3),
                    $this->prop('rw_timeout', 5),
                    $this->prop('context', null),
                    $this->prop('keep_alive', false),
                    $this->prop('heartbeat', 20),
                    $this->prop('channel_rpc_timeout', 0.0)
                );
                
                // setup Rest API Provider, only port is changed
                RabbitMQRest::setup(
                    $this->prop('host'),
                    $this->prop('port.api'),
                    $this->prop('user'),
                    $this->prop('password')
                );
                
                if(strpos($this->prop('host'), 'localhost') === false && $this->prop('https', false)) {
                    RabbitMQRest::useHttps();
                }
                
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