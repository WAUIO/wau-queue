<?php
namespace WAUQueue\Contracts;

/**
 *
 * @author Andrianina OELIMAHEFASON
 */
interface Factory {
    /**
	 * Resolve a queue connection instance.
     *
	 * @param  string  $name
	 * @return \Illuminate\Contracts\Queue\Queue
	 */
	public function connection();
}
