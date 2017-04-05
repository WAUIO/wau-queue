<?php
namespace WAUQueue\Contracts\Job;

use PhpAmqpLib\Message\AMQPMessage;

interface InterfaceJob {

    /**
	 * Fire the job.
	 *
     * @param mixed|AMQPMessage $message
     *
	 * @return boolean
	 */
	public function fire($message);
}
