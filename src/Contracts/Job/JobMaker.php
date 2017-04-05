<?php

namespace WAUQueue\Contracts\Job;

use WAUQueue\Contracts\Job\AbstractJobMaker;
use WAUQueue\Jobs\DefaultJob;

/**
 * Description of JobMaker
 *
 * @deprecated
 * (No more in use from 2.x)
 *
 * @author Andrianina OELIMAHEFASON
 */
class JobMaker extends AbstractJobMaker{
    
    public function makeJob($type) {
        if ($type === 'default') {
            return new DefaultJob();
        } else {
            return null;
        }
    }

}
