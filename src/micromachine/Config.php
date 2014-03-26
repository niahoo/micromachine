<?php

namespace micromachine;

class Config extends Ar {

    static function load($vars=array()) {
        $conf = new self($vars);
        $conf->set_base_URL();
        return $conf;
    }

    private function set_base_URL() {
        $this->set('base_URL',
            $this->get_default('protocol', 'http://')
            . $this->get_default('host_name', $_SERVER['HTTP_HOST'])
            . $this->get_default('base_path', '')
        );
    }
}


