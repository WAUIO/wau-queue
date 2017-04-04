<?php namespace WAUQueue\Contracts\Message;


interface MessageInterface
{
    /**
     * Get the raw message
     *
     * @return mixed
     */
    public function raw();
    
    /**
     * Get the complete config
     *
     * @return array
     */
    public function getConfig();
    
    /**
     * Get the complete headers
     *
     * @return mixed
     */
    public function getHeaders();
}