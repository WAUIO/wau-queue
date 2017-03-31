<?php
namespace WAUQueue\Helpers;



/**
 * Description of RabbitMQHelpers
 *
 * @author Andrianina OELIMAHEFASON
 */
trait RabbitMQHelpers {
    public function addPriority($options, &$headers) {
        if (isset($options['priority'])) {
            $headers['priority'] = (int) $options['priority'];
        }
    }
    
    public function setPriorityParams($params = [] , $configs) {
        if (isset($configs['priority'])) {
            $params['x-max-priority'] = $configs['priority'];
        }
        return $params;
    }
}
