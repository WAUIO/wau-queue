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
    
    public function push($item, $key = null) {
        if(is_null($key)) {
            $this->dataset[] = $item;
        } else $this->array_set($this->dataset, $key, $item);
        
        return $this;
    }
    
    public function filter(callable $filter) {
        return new self(
            array_filter($this->dataset, $filter)
        );
    }
    
    public function values(){
        return new self(
            array_values($this->dataset)
        );
    }
    
    public function get($key){
        return $this->offsetGet($key);
    }
    
    public function last() {
        return $this->count() > 0 ? $this->get($this->count() - 1) : null;
    }
    
    public function first() {
        return $this->count() > 0 ? $this->values()->get(0) : null;
    }
    
    public function where($key, $value) {
        return $this->filter(function($item) use($key, $value){
            return $this->dataValue($item, $key) == $value;
        });
    }
    
    public function whereNot($key, $value) {
        return $this->filter(function($item) use($key, $value){
            return $this->dataValue($item, $key) != $value;
        });
    }
    
    public function groupBy($key){
        $temp = array();
        
        $groups = array_unique(array_map(function($item) use($key){
            return $this->dataValue($item, $key);
        }, $this->dataset));
    
        foreach ($groups as $group) {
            $temp[$group] = new self(array_filter($this->dataset, function($item) use($key, $group){
                return $group == $this->dataValue($item, $key);
            }));
        }
        
        return new self($temp);
    }
    
    protected function dataValue($item, $key) {
        return is_array($item) ? $this->array_get($item, $key) : (is_object($item) ? $this->object_get($item, $key) : $item);
    }
    
    public function count(){
        return count($this->dataset);
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