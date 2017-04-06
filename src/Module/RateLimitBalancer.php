<?php namespace WAUQueue\Module;


use WAUQueue\Contracts\Module\ModuleAbstract;
use WAUQueue\Helpers\BashOutput\BashOutputAbilityTrait;

/**
 * Class RateLimitBalancer
 *
 * It will change worker behavior according to
 * rate limit value / rate limit remaining
 *
 * @package WAUQueue\Module
 */
class RateLimitBalancer extends ModuleAbstract
{
    use BashOutputAbilityTrait;
    
    protected $rateLimit;
    
    /**
     * @var Int
     */
    protected $level;
    
    public function __construct($rateLimit, $level) {
        $this->rateLimit = $rateLimit;
        $this->level     = $level;
        $this->prefix    = '[module.RateLimitBalancer] ';
        $this->registerDefaultStyles();
    }
    
    public function balance($value) {
        if($this->acceptShutdown($value)){
            // should shutdown now
            $this->output("Rate limit level is over. [Limit={$this->rateLimit}, level={$this->level}, Value={$value}]", 'error');
            exit;
        } else {
            // continue...
        }
    }
    
    protected function acceptShutdown($value) {
        return $this->level >= 100 * $value / $this->rateLimit;
    }
}