<?php

namespace WAUQueue\Contracts;

interface QueueInterface {

	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return mixed
	 */
	public function push($job, $data = '', $queue = null);

	/**
	 * Pop the next job off of the queue.
	 *
	 * @param  string  $queue
	 * @return \Illuminate\Contracts\Queue\Job|null
	 */
	public function pop($queue = null);
    
    /**
     * Push a new raw onto the queue.
     * 
     * @param string $payload
     * @param mixed $data
     * @param string $queue
     * @param array $options
     * @return mixed
     */
    public function pushRaw($payload, $data = '', $queue = null, $options = array() );

}

