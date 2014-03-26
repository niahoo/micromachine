<?php

namespace micromachine;

class Context extends Ar {

    private $observers;

    public static function create($vars) {
        $c = new self($vars);
        $c->observers = new Ar(array());
        $c->set('templates_dirs',array());
        return $c;
    }

    public function init_modules() {
        $mods = $this->conf->get_default('load_modules',array());
        foreach($mods as $class) {
            $this->load_module($class);
        }
    }

    public function load_module($class) {
        Module::load($class,$this);
        $sofar = $this->get('templates_dirs');
        $addtemplates_dirs = Module::get_templates_dirs($class);
        $this->set('templates_dirs', array_merge($sofar,$addtemplates_dirs));
    }

    public function __call($method, $args) {
        return call_user_func_array($this->$method, $args);
    }

    public function observe($event_name,$callback) {
        $this->observers->push_to($event_name, $callback);
    }

    public function fire($event_name, $data=null) {
        $handlers = $this->observers->get_default($event_name,array());
        foreach ($handlers as $fun) {
            call_user_func($fun,$this,$data);
        }
    }

    public function url($n,$ps=array()) {
        return $this->get('router')->generate($n,$ps);
    }
}
