<?php namespace WAUQueue\Helpers;


trait Utilities
{
    /**
     * Fetch content from array
     *
     * @param array $array
     * @param       $key
     * @param null  $default
     *
     * @source Laravel Helpers
     *
     * @return mixed|null
     */
    public function array_get(array $array, $key, $default = null) {
        if (! array_accessible($array)) {
            return value($default);
        }
    
        if (is_null($key)) {
            return $array;
        }
    
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
    
        foreach (explode('.', $key) as $segment) {
            if (array_accessible($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }
    
        return $array;
    }
    
    /**
     * @param $array
     * @param $key
     * @param $value
     *
     * @source Laravel Helpers
     *
     * @return $this
     */
    public function array_set(&$array, $key, $value) {
        if (is_null($key)) {
            return $array = $value;
        }
    
        $keys = explode('.', $key);
    
        while (count($keys) > 1) {
            $key = array_shift($keys);
        
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
        
            $array = &$array[$key];
        }
    
        $array[array_shift($keys)] = $value;
    
        return $array;
    }
    
    public function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }
    
        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return value($default);
            }
        
            $object = $object->{$segment};
        }
    
        return $object;
    }
    
    public function init_class_property($object, $property, $initialValue) {
        if(!$object->{$property}){
            $object->{$property} = $initialValue;
        }
    }
}

if(!function_exists('array_accessible')) {
    function array_accessible($array){
        return is_array($array) || $array instanceof \ArrayAccess;
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}