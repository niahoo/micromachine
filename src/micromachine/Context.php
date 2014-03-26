<?php

namespace micromachine;

class Context extends Ar {

    private $observers;

    public static function create($vars) {
        $c = new self($vars);
        $c->observers = new Ar(array());
        return $c;
    }

    public function init_modules() {
        $mods = $this->conf->get_default('load_modules',array());
        foreach($mods as $n) {
            $this->load_module($n);
        }
    }

    public function load_module($name) {
        $rc = new \ReflectionClass($name);
        $methods = $rc->getMethods();
        foreach ($methods as $m) {
            if('init' === $m->name) {
                $name::init($this);
            }
            if (preg_match('/^event_(.*)/', $m->name, $matches)) {
                $this->observe($matches[1],array($name,$m->name)); // Must be a static function
            }
        }
    }

    public function __call($method, $args) {
        return call_user_func_array($this->$method, $args);
    }

    public function observe($event_name,$callback) {
        $this->observers->push_to($event_name, $callback);
    }

    public function fire($event_name, $data=null) {
        register_shutdown_function(function()use($event_name) {
            r("firing event $event_name");
        });
        $handlers = $this->observers->get_default($event_name,array());
        foreach ($handlers as $fun) {
            call_user_func($fun,$this,$data);
        }
    }
}
