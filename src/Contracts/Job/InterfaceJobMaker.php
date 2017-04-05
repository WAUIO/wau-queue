<?php namespace WAUQueue\Contracts\Job;

/**
 * Interface InterfaceJobMaker
 *
 * @deprecated
 * (No more in use in 2.x)
 *
 * @package WAUQueue\Contracts\Job
 */
interface InterfaceJobMaker {
    
    /**
     * @param mixed $type type of the job
     */
    public function makeJob($type);
}
