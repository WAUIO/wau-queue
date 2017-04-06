<?php namespace WAUQueue\Helpers\BashOutput;


use WAUQueue\Helpers\CollectionSet;

trait BashOutputAbilityTrait
{
    /**
     * @var \WAUQueue\Helpers\CollectionSet
     */
    protected $styles;
    
    protected $defaultStyle = 'default';
    
    protected function setStyle($name, BashStyle $style) {
        $this->styles[$name] = $style;
    }
    
    public function registerDefaultStyles() {
        $this->styles = new CollectionSet();
        $this->setStyle('default', new BashStyle());
        $this->setStyle('error', new BashStyle('41', '1;37'));
        $this->setStyle('warning', new BashStyle('43', '0;31'));
        $this->setStyle('info', new BashStyle('', '0;32'));
    }
    
    public function output($text, $style = null) {
        if(is_null($style)) $style = $this->defaultStyle;
        
        $style = !is_null($style) ? $this->styles->get($style) : new BashStyle('', '');
        $this->writeln($style->color($text));
    }
    
    public function write($text) {
        print_r($text);
    }
    
    public function writeln($text) {
        $this->write($text . "\n");
    }
    
    public function plain($text) {
        return strip_tags($text);
    }
}