<?php namespace WAUQueue\Adapter\RabbitMQ\Rest;


class Response
{
    public $info;
    
    public $status;
    
    public $headers;
    
    public $raw;
    
    public $json;
    
    public function __construct($raw, $headers = array(), $status = 200, $info = array()) {
        $this->raw     = $raw;
        $this->headers = $headers;
        $this->status  = intval($status);
        $this->info    = $info;
        $this->json    = json_decode($this->raw);
    }
    
    /**
     * @param array $response
     *
     * @return \WAUQueue\Adapter\RabbitMQ\Rest\Response
     */
    public static function httpBuild(array $response) {
        list($info, $status, $headers, $raw) = array_values($response);
        
        return new self($raw, $headers, $status, $info);
    }
}