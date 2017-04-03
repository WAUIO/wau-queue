<?php namespace WAUQueue\Helpers;


trait Utilities
{
    /**
     * Fetch content from array
     *
     * @param array $container
     * @param       $key
     * @param null  $default
     *
     * @return mixed|null
     */
    public function array_get(array $container, $key, $default = null) {
        return array_key_exists($key, $container) ? $container[$key] : $default;
    }
}