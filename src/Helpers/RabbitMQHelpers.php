<?php namespace WAUQueue\Helpers;

/**
 * Description of RabbitMQHelpers
 *
 * @author Andrianina OELIMAHEFASON
 */
trait RabbitMQHelpers {
    
    /**
     * Patch the headers according to options about the priotity order
     *
     * @param $options
     * @param $headers
     */
    public function addPriority($options, &$headers) {
        if (isset($options['priority'])) {
            $headers['priority'] = (int) $options['priority'];
        }
    }
    
    /**
     * @param array $params
     * @param       $configs
     *
     * @return array
     */
    public function setPriorityParams($params = [], $configs) {
        if (isset($configs[ 'priority' ])) {
            $params[ 'x-max-priority' ] = $configs[ 'priority' ];
        }
        
        return $params;
    }
}
