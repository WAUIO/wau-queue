<?php
namespace WAUQueue\Contracts\Job;

interface InterfaceJob {

    /**
	 * Fire the job.
	 *
     * @param mixed $job Job name
     * @param array $data The data stored
	 * @return boolean
	 */
	public function fire($job, $data);
}
