<?php


namespace WAUQueue\Contracts\Job;

interface InterfaceJobMaker {
    
    /**
     * @param mixed $type type of the job
     */
    public function makeJob($type);
}
