<?php

namespace micromachine;

class Context extends Ar {

    public function load_module($name) {
        $rc = new \ReflectionClass($name);
        $methods = $rc->getMethods();
        r($methods);
    }

    public function __call($method, $args) {
        return call_user_func_array($this->$method, $args);
    }

    public function fire($event_name, $data=null) {
        $a = func_get_args();
        r($a,'Fire event');
    }
}
