<?php namespace WAUQueue\Contracts\Message;


use WAUQueue\Connectors\ConnectorInterface;
use WAUQueue\Helpers\PropertiesTrait;
use WAUQueue\Helpers\Utilities;

/**
 * Class BrokerAbstract
 *
 * @package WAUQueue\Contracts\Message
 */
abstract class BrokerAbstract
{
    use Utilities, PropertiesTrait;
    
    /**
     * @var ConnectorInterface
     */
    protected $connector;
    
    public function __construct(ConnectorInterface $connector, $options = array()) {
        $this->connector  = $connector;
        $this->properties = array_merge(array(
            'accept-multi-queues' => true
        ), $options);
    }
    
    /**
     * @return \WAUQueue\Connectors\ConnectorInterface
     */
    protected function connect() {
        if (!$this->connector->ready()) {
            $this->connector->connect([]);
        }
        
        return $this->connector;
    }
    
    /**
     * Retrieve option by key
     *
     * @param $key
     *
     * @return null
     */
    public function option($key) {
        return $this->prop($key);
    }
}