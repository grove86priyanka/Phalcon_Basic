<?php

namespace App\Library\FormElements;
//namespace Phalcon\Forms\Element;

class Tag extends \Phalcon\Forms\Element
{
    protected $tagName;
    protected $tagParameters;

    public function __construct($name, $attributes = null, $tagName = null, $tagParameters = null) {
        $this->tagName = $tagName;
        $this->tagParameters = $tagParameters;
        
        parent::__construct($name, $attributes);
    }

    public function setParameters($tagParameters = null) {
        $this->tagParameters = $tagParameters;
    }
    
    public function getParameters() {
        return $this->tagParameters;
    }
    
    public function render($attributes = null)
    {
//        return;
        
        if (!$this->tagName) throw new \Phalcon\Forms\Exception("Missing Tag Name");
        else if (!method_exists(\Phalcon\Tag::class, $this->tagName)) throw new \Phalcon\Forms\Exception("Method \"{$this->tagName}\" does not exists in \Phalcon\Tag class");

        $tag = new \Phalcon\Tag();
        $html = $tag->linkTo($this->tagParameters);
        if ($this->tagParameters) $html = $tag->{$this->tagName}($this->tagParameters);
        else $html = $tag->{$this->tagName}();
        
        echo $html;
    }
}

?>