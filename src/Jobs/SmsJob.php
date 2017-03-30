<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WAUQueue\Jobs;

use WAUQueue\Contracts\Job;
/**
 * Description of SmsJob
 *
 * @author Andrianina OELIMAHEFASON
 */
class SmsJob implements Job {

    public function attempts() {
        
    }

    public function delete() {
        
    }

    public function fire($job, $data) {
        echo "Message  \"{$data['message']}\" sent." . PHP_EOL;
    }

    public function getName() {
        
    }

    public function getQueue() {
        
    }

    public function release($delay = 0) {
        
    }

}
