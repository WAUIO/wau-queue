<?php namespace WAUQueue\Helpers\BashOutput;


use WAUQueue\Helpers\CollectionSet;
use WAUQueue\Helpers\Utilities;

trait BashOutputAbilityTrait
{
    use Utilities;
    
    /**
     * @var \WAUQueue\Helpers\CollectionSet
     */
    protected $styles;
    
    protected $defaultStyle = 'default';
    
    protected $prefix = '';
    
    protected function setStyle($name, BashStyle $style) {
        $this->styles[$name] = $style;
    }
    
    public function registerDefaultStyles() {
        $this->styles = new CollectionSet();
        
        $this->setStyle('default', new BashStyle());
        $this->setStyle('error', new BashStyle('41', '1;37'));
        $this->setStyle('warning', new BashStyle('43', '0;31'));
        $this->setStyle('info', new BashStyle('', '0;32'));
        $this->setStyle('alert', new BashStyle('', '0;34'));
        $this->setStyle('highlight', new BashStyle('40', '1;37'));
        
        return $this;
    }
    
    public function output($text, $style = null) {
        $this->init_class_property($this, 'styles', new CollectionSet());
        
        if(is_null($style)) $style = $this->defaultStyle;
        
        $style = !is_null($style) ? $this->styles->get($style) : new BashStyle('', '');
        $this->writeln($style->color("{$this->prefix}{$text}"));
    }
    
    public function write($text) {
        print("{$text}");
    }
    
    public function writeln($text) {
        $this->write($text . "\n");
    }
}