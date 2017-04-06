<?php namespace WAUQueue\Contracts\Module;


use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Helpers\Utilities;

trait ModulableHelperTrait
{
    use Utilities;
    
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
        $this->init_class_property($this, 'modules', new CollectionSet());
    
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
        
        $module->setContext($this);
        
        $this->modules[get_class($module)] = $module;
        
        return $this;
    }
    
    /**
     * @inheritdoc
     */
    public function module($action, $params = array()) {
        list($module, $method) = explode('@', $action);
        $module = $this->modules->get($module);
        
        if($module instanceof ModuleInterface) {
            // module found in the dataset
            if($method && method_exists($module, $method)) {
                return call_user_func_array(array($module, $method), $params);
            } else {
                // module found but the action is not valid
            }
        } else {
            // no module found
        }
        
        return null;
    }
}