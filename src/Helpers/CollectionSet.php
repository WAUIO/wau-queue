<?php namespace WAUQueue\Helpers;


class CollectionSet implements \IteratorAggregate, \ArrayAccess
{
    use Utilities;
    
    protected $dataset = array();
    
    public function __construct($dataset = array()) {
        $this->dataset = $dataset;
    }
    
    //--------------------- Usage methods --------------------//
    public function each(callable $function, $userdata = null) {
        array_walk($this->dataset, $function, $userdata);
        
        return $this;
    }
    
    public function map(callable $function){
        return new self(array_map($function, $this->dataset));
    }
    
    //------------------- Interface method -------------------//
    public function getIterator() {
        return new \ArrayIterator($this->dataset);
    }
    
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->dataset);
    }
    
    public function offsetGet($offset) {
        return $this->array_get($this->dataset, $offset);
    }
    
    public function offsetSet($offset, $value) {
        return $this->array_set($this->dataset, $offset, $value);
    }
    
    public function offsetUnset($offset) {
        if($this->offsetExists($offset)){
            unset($this->dataset[$offset]);
        }
    }
    
    //------------------- Additioanl method -------------------//
    /**
     * Make data content as array as well
     *
     * @return array
     */
    public function toArray(){
        return $this->dataset;
    }
    
    public function toJson(){
        return json_encode($this->toArray());
    }
}