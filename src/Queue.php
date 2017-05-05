<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WAUQueue;
use WAUQueue\Contracts\QueueInterface;
/**
 * Description of Queue
 *
 * @deprecated
 * (No more in use on 2.x)
 *
 * @author Andrianina OELIMAHEFASON
 */
abstract class Queue implements QueueInterface {
    
   /**
	 * Create a payload string from the given job and data.
	 *
	 * @param  string  $job
	 * @param  mixed   $data
	 * @param  string  $queue
	 * @return string
	 */
	protected function createPayload($job, $data = '', $queue = null)
	{
		if (is_object($job))
		{
            // @todo : set the right job call
			return json_encode([
				'job' => 'WAUQueue\Queue\CallQueuedHandler@call',
				'data' => ['command' => serialize($job)],
			]);
		}

		return json_encode($this->createPlainPayload($job, $data));
	}
    
    /**
	 * Create a typical, "plain" queue payload array.
	 *
	 * @param  string  $job
	 * @param  mixed  $data
	 * @return array
	 */
	protected function createPlainPayload($job, $data)
	{
		return ['job' => $job, 'data' => $data];
	}
}
