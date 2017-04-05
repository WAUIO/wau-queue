<?php namespace WAUQueue\Helpers;

/**
 * Class PropertiesTrait
 *
 * @package WAUQueue\Helpers
 */
trait PropertiesTrait
{
    /**
     * Properties bag
     *
     * @var array
     */
    protected $properties = array();
    
    /**
     * Set a property
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setProperty($key, $value) {
        $this->properties[ $key ] = $value;
        
        return $this;
    }
    
    /**
     * Fetch a property value
     *
     * @param      $key
     * @param null $default
     *
     * @return null
     */
    public function prop($key, $default = null) {
        return array_key_exists($key, $this->properties)
            ? $this->properties[ $key ] : $default;
    }
    
    /**
     * Get all properties value
     *
     * @return array
     */
    public function props() {
        return $this->properties;
    }
}