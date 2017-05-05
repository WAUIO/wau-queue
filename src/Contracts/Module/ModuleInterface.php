<?php namespace WAUQueue\Contracts\Module;


interface ModuleInterface
{
    /**
     * @param ModulableInterface $context
     *
     * @return mixed
     */
    public function setContext(ModulableInterface $context);
    
    /**
     * @return ModulableInterface
     */
    public function context();
}