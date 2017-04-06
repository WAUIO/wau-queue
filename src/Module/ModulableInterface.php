<?php namespace WAUQueue\Module;


interface ModulableInterface
{
    /**
     * Add a module to a modulable object
     *
     * @param ModuleInterface $module
     *
     * @return mixed
     */
    public function addModule(ModuleInterface $module);
}