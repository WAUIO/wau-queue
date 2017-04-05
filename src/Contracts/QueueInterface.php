<?php namespace WAUQueue\Contracts;


/**
 * Interface QueueInterface
 *
 * @deprecated
 * (No more in use on 2.x)
 *
 * @package WAUQueue\Contracts
 */
interface QueueInterface {

	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @param  array   $options
	 * @return mixed
	 */
	public function push($job, $data = '', $queue = null, $options = array());

	/**
	 * Pop the next job off of the queue.
	 *
	 * @param  string  $queue
	 */
	public function pop($queue = null);
    
    /**
     * Push a new raw onto the queue.
     * 
     * @param string $payload
     * @param string $queue
     * @param array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, $options = array() );

}

