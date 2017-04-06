<?php namespace WAUQueue\Module;


use WAUQueue\Contracts\Module\ModuleAbstract;

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
    /**
     * Rate limit value
     *
     * @var Int
     */
    protected $rateLimit;
    
    /**
     * Level to not exceed (in %, so between 0-99)
     * 10 will be set as default if this range is not respected
     *
     * @var Int
     */
    protected $level;
    
    public function __construct($rateLimit, $level) {
        $this->rateLimit = $rateLimit;
        $this->level     = ($level >= 0 && $level < 100) ? $level : 10;
        $this->prefix    = '[Module.RateLimitBalancer] ';
        $this->registerDefaultStyles();
    }
    
    public function balance($value) {
        $this->output("Remaining Rate Limit {$value}", 'info');
        
        if ($this->invokeShutdown($value)) {
            // should shutdown now
            $this->output("Rate limit level is over. [Limit={$this->rateLimit}, level={$this->level}%, Value={$value}]", 'error');
            return true;
        }
        
        return false;
    }
    
    protected function invokeShutdown($value) {
        return $this->level >= 100 * $value / $this->rateLimit;
    }
}