<?php namespace WAUQueue\Contracts\Module;


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
    
    /**
     * Call action on a specific module
     *
     * @param       $action
     * @param array $params
     *
     * @return mixed
     */
    public function module($action, $params = array());
}