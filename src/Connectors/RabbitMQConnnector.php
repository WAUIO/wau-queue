<?php
namespace WAUQueue\Connectors;

use WAUQueue\RabbitMQQueue; 
use WAUQueue\Connectors\ConnectorInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use WAUQueue\Contracts\Factory;
/**
 * Description of RabbitMQConnnector
 *
 * @author Andrianina OELIMAHEFASON
 */
class RabbitMQConnnector implements ConnectorInterface, Factory{
    
    /** @var \WAUQueue\Connectors\AMQPStreamConnection */
    private static $connection;

    /**
     * @var RabbitMQQueue 
     */
    public static $queue;
    
    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \WAUQueue\RabbitMQQueue RabbitMQQueue
     */
    public function connect(array $config)
    {
        $this->setAMQPSStreamConnection($config);
        $this->setQueue($config);
        return static::$queue;
    }
    
    public function connection($name = null)
    {
        return static::$connection;
    }
    
    private function setAMQPSStreamConnection ($config) {
        if (is_null(static::$connection)) {
            // create connection witsh AMQP
            static::$connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['login'],
                $config['password'],
                $config['vhost']
            );
        } 
    }
    
    private function setQueue($config) {
        if(is_null(static::$queue)) {
            static::$queue = new RabbitMQQueue(
                static::$connection,
                $config
            );
        }
    }
}
