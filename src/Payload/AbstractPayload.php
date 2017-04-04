<?php namespace WAUQueue\Payload;

use WAUQueue\Contracts\Message\MessageInterface;
use WAUQueue\Helpers\PropertiesTrait;
use WAUQueue\Helpers\Utilities;

/**
 * Class AbstractPayload
 *
 * @package WAUQueue\Payload
 */
abstract class AbstractPayload implements MessageInterface
{
    use PropertiesTrait, Utilities;
    
    /**
     * Headers content
     *
     * @var array
     */
    protected $headers = array();
    
    /**
     * Payload to send
     *
     * @var mixed
     */
    protected $payload;
    
    public function __construct($payload, $headers = array(), $config = array()) {
        $this->payload    = $payload;
        $this->headers    = $headers;
        $this->properties = $config;
    }
    
    public abstract function raw();
    
    public function getConfig() {
        return $this->properties;
    }
    
    public function getHeaders() {
        return $this->headers;
    }
    
    public function setPayload($payload) {
        $this->payload = $payload;
        
        return $this;
    }
    
    public function setHeader($key, $value) {
        return $this->array_set($this->headers, $key, $value);
    }
    
    public function setConfig($key, $value) {
        return $this->setProperty($key, $value);
    }
    
}