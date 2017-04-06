<?php namespace WAUQueue\Contracts\Module;

use WAUQueue\Helpers\BashOutput\BashOutputAbilityTrait;


/**
 * Class ModuleAbstract
 *
 * @package WAUQueue\Module
 */
class ModuleAbstract implements ModuleInterface
{
    use ModulableHelperTrait, BashOutputAbilityTrait;
    
    /**
     * The object where the module is implemented
     *
     * @var ModulableInterface
     */
    protected $context;
    
    /**
     * Set the context from public scope
     *
     * @param ModulableInterface $context
     *
     * @return $this
     */
    public function setContext(ModulableInterface $context) {
        $this->context = $context;
        
        return $this;
    }
    
    /**
     * @return ModulableInterface
     */
    public function context() {
        return $this->context;
    }
}