<?php namespace WAUQueue;


use WAUQueue\Adapter\RabbitMQ;
use WAUQueue\Contracts\Message\BrokerInterface;
use WAUQueue\Exception\UnknownDriver;

/**
 * Class Bus
 *
 * Main Bus object that will handles the queue system
 * THis class is the builder class which build the abstraction
 * of the drived server/broker
 *
 * @package WAUQueue
 */
class Bus
{
    /**
     * Config for the broker
     *
     * @var array
     */
    protected $config;
    
    /**
     * Type of Broker to manage
     *
     * @var string
     */
    protected $driverType;
    
    /**
     * Concrete instance of the broker
     *
     * @var null|BrokerInterface
     */
    protected $broker;
    
    public function __construct($driver, $config = array()) {
        $this->driverType = $driver;
        $this->config     = $config;
        $this->setBroker();
    }
    
    /**
     * @throws UnknownDriver
     */
    protected function setBroker() {
        switch ($this->driverType) {
            case 'rabbit-mq':
                $this->broker = new RabbitMQ\BrokerServiceBuilder(
                    new RabbitMQ\Connector($this->config)
                );
                break;
            
            default:
                throw new UnknownDriver("Driver [{$this->driverType}] is not recognized by system");
                break;
        }
    }
    
    /**
     * @return null|BrokerInterface
     */
    public function getBroker() {
        return $this->broker;
    }
    
    /**
     * Magic method that will use directly the concrete broker instance
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed|null
     */
    public function __call($name, $arguments) {
        if (method_exists($this->broker, $name)) {
            return call_user_func_array(array($this->broker, $name), $arguments);
        }
        
        return null;
    }
}