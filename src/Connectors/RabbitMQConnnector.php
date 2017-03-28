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
        return self::$queue;
    }
    
    public function connection()
    {
        return $this->connection;
    }
    
    private function setAMQPSStreamConnection ($config) {
        if (is_null(self::$connection)) {
            // create connection witsh AMQP
            self::$connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['login'],
                $config['password'],
                $config['vhost']
            );
        } 
    }
    
    private function setQueue($config) {
        if(is_null(self::$queue)) {
            self::$queue = new RabbitMQQueue(
                self::$connection,
                $config
            );
        }
    }
}
