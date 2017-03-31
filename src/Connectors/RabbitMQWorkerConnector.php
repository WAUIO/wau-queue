<?php
namespace WAUQueue\Connectors;

use WAUQueue\Connectors\ConnectorInterface;
use WAUQueue\Contracts\Factory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
/**
 * Description of RabbitMQWorkerConnctor
 *
 * @author Andrianina OELIMAHEFASON
 */
class RabbitMQWorkerConnector implements ConnectorInterface, Factory {
    
    private $connection;
    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \WAUQueue\RabbitMQQueue RabbitMQQueue
     */
    public function connect(array $config)
    {
        $this->setConnection($config);
    }
    
    public function connection()
    {
        return $this->connection;
    }
    
    public function setConnection($config) {
        $this->connection =  new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['login'],
            $config['password'],
            isset($config['vhost']) ? $config['vhost'] : null
        );
    }
}
