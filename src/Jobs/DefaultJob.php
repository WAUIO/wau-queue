<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WAUQueue\Jobs;

use WAUQueue\Contracts\Job\AbstractJob;
/**
 * Description of SmsJob
 *
 * @author Andrianina OELIMAHEFASON
 */
class DefaultJob extends AbstractJob {
    
    public function fire($job, $data) {
        echo "Default Job sent  \"". serialize($data)."\" sent." . PHP_EOL;
        return true;
    }
}
