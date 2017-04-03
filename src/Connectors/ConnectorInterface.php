<?php

namespace WAUQueue\Connectors;

/**
 *  ConnectorInterface
 * @author Andrianina OELIMAHEFASON
 */
interface ConnectorInterface {
    
    /**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 */
	public function connect(array $config);
    
    /**
     * Check the ready state
     *
     * @return bool
     */
	public function ready();
}
