<?php namespace WAUQueue\Adapter\RabbitMQ\Rest;


use WAUQueue\Adapter\RabbitMQ\Rest\Http;

class RabbitMQRest
{
    /**
     * @var Http
     */
    public static $http;
    
    protected static $secureHttp = false;
    
    public static $credentials = array(
        'host' => '',
        'port' => '',
        'user' => '',
        'password' => '',
    );
    
    /**
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     */
    public static function setup($host, $port, $user, $password) {
        self::$credentials[ 'host' ]     = $host;
        self::$credentials[ 'port' ]     = $port;
        self::$credentials[ 'user' ]     = $user;
        self::$credentials[ 'password' ] = $password;
        
        self::$http = new Http(array(
            'auth' => array(
                'type' => 'basic',
                'username' => $user,
                'password' => $password,
            )
        ));
    }
    
    public static function useHttps($value = true) {
        self::$secureHttp = $value;
    }
    
    /**
     * @param       $path
     * @param array $query
     *
     * @return string
     */
    public static function url($path, $query = array()) {
        $protocol = "http" . (self::$secureHttp ? 's' : '');
    
        return sprintf("%s://%s:%s/%s?%s", $protocol,
            self::$credentials[ 'host' ],
            self::$credentials[ 'port' ],
            $path,
            http_build_query($query)
        );
    }
    
    /**
     * @param       $path
     * @param array $params
     * @param array $options
     *
     * @return \WAUQueue\Adapter\RabbitMQ\Rest\Response
     */
    public static function get($path, $params = array(), $options = array()){
        return Response::httpBuild(self::$http->get(self::url($path), $params, $options));
    }
    
    /**
     * @param       $path
     * @param array $params
     * @param array $options
     *
     * @return \WAUQueue\Adapter\RabbitMQ\Rest\Response
     */
    public static function put($path, $params = array(), $options = array()){
        return Response::httpBuild(self::$http->put(self::url($path), $params, $options));
    }
    
    /**
     * @param       $path
     * @param array $params
     * @param array $options
     *
     * @return \WAUQueue\Adapter\RabbitMQ\Rest\Response
     */
    public static function delete($path, $params = array(), $options = array()){
        return Response::httpBuild(self::$http->delete(self::url($path), $params, $options));
    }
    
    /**
     * @param       $path
     * @param array $params
     * @param array $options
     *
     * @return \WAUQueue\Adapter\RabbitMQ\Rest\Response
     */
    public static function post($path, $params = array(), $options = array()){
        return Response::httpBuild(self::$http->post(self::url($path), $params, $options));
    }
}