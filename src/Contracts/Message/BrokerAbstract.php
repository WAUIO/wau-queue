<?php namespace WAUQueue\Contracts\Message;


use WAUQueue\Connectors\ConnectorInterface;

/**
 * Class BrokerAbstract
 *
 * @package WAUQueue\Contracts\Message
 */
abstract class BrokerAbstract
{
    /**
     * @var ConnectorInterface
     */
    protected $connector;
    
    protected $channel;
    
    public function __construct(ConnectorInterface $connector) {
        $this->connector = $connector;
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
}