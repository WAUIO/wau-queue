<?php namespace WAUQueue\Module;


use WAUQueue\Helpers\CollectionSet;

trait ModulableHelperTrait
{
    /**
     * @var \WAUQueue\Helpers\CollectionSet
     */
    protected $modules;
    
    /**
     * Initiate the modules container and ability
     *
     * @param array $modules
     *
     * @return $this
     */
    protected function initModules($modules = array()) {
        if(!$this->modules)
            $this->modules = new CollectionSet();
    
        foreach ($modules as $module) {
            $this->addModule($module);
        }
        
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function addModule(ModuleInterface $module) {
        if (!$this->modules) {
            $this->initModules();
        }
        
        $this->modules[get_class($module)] = $module;
    }
}