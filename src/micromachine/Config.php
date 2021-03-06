<?php

namespace micromachine;

class Config extends Ar {

    static function load($vars=array()) {

        $conf = new self($vars);
        $KEY = $conf->get_default('CONF_KEY', false);
        //@todo ici récup la conf AVEC les modules chargés depuis APC
        // mais il faudra donc fournir une fonction au contexte pour inclure
        // un module qui n'aurait pas d'initialisation car celui-ci ne
        // sera pas inclus pour regarder ses méthodes
        $conf->load_modules();
        $conf->load_controllers();
        $conf->load_models();
        $conf->load_exceptions();
        $conf->load_mods_to_init();
        $conf->load_libs();
        $conf->load_templates_dirs();

        //@todo APC cache ?

        $conf->set('load_mode','file');
        $conf->set_base_URL();
        return $conf;
    }

    private function load_modules() {
        $modules = array();
        $to_load = $this->get_default('load_modules',array());
        foreach($to_load as $key => $value) {
            if (is_numeric($key)) {
                $module_name = $value;
                $force_dir = null;
            } else {
                $module_name = $key;
                $force_dir = $value;
            }
            $modules[strtolower($module_name)] = Module::load($module_name, $this, $force_dir);
        }
        $this->set('modules', $modules);
    }

    public function module_loaded($name) {
        return isset($this->modules[$name]);
    }

    public function load_module($name, $keyname=-1) {
        if (is_numeric($keyname)) {
            $module_name = $name;
            $force_dir = null;
        } else {
            $module_name = $keyname;
            $force_dir = $name;
        }
        if (! $this->module_loaded($module_name)) {
            $old = $this->modules;
            $old[strtolower($module_name)] = Module::load($module_name, $this, $force_dir);
            $this->set('modules', $old);
        }
    }
    //@todo refactor avec controllers, models, libs, exceptions
    private function load_controllers() {
        $controllers = array();
        foreach($this->modules as $module) {
            $controllers = array_merge($controllers, $module->get_controllers());
        }
        $this->set('controllers', new Ar($controllers));
    }

    private function load_models() {
        $models = array();
        foreach($this->modules as $module) {
            $models = array_merge($models, $module->get_models());
        }
        $this->set('models', new Ar($models));
    }

    private function load_exceptions() {
        $exceptions = array();
        foreach($this->modules as $module) {
            $exceptions = array_merge($exceptions, $module->get_exceptions());
        }
        $this->set('exceptions', new Ar($exceptions));
    }

    private function set_base_URL() {
        $this->set('base_URL',
            $this->get_default('protocol', 'http://')
            . $this->get_default('host_name', $_SERVER['HTTP_HOST'])
            . $this->get_default('base_path', '')
        );
    }

    private function load_libs() {
        $libs = array();
        foreach($this->modules as $module) {
            $libs = array_merge($libs, $module->get_libs());
        }
        $this->set('libs', new Ar($libs));
    }

    public function set_routes($router) {
        foreach($this->modules as $module) {
            $module->set_routes($router);
        }
    }

    public function require_controller($name) {
        $path = $this->controllers->get_default($name,false);
        if(false === $path) {
            throw new \InvalidArgumentException("Controller '$name' not found");
        }
        else {
            require_once $path;
        }
    }

    public function require_model($name) {
        $path = $this->models->get_default($name,false);
        if(false === $path) {
            throw new \InvalidArgumentException("Model '$name' not found");
        }
        else {
            require_once $path;
        }
    }

    public function require_exception($name) {
        $path = $this->exceptions->get_default($name,false);
        if(false === $path) {
            return false;
        }
        else {
            require_once $path;
            return true;
        }
    }


    public function require_module_file($name) {
        require_once $this->modules[$name]->main_file_path();
    }

    public function load_mods_to_init() {
        $mods_to_init = array();
        foreach($this->modules as $name => $module) {
            if($module->has_main_file && $module->has_init_method) {
                $mods_to_init[] = $name;
            }
        }
        $this->set('mods_to_init', $mods_to_init);
    }

    public function load_templates_dirs() {
        $tpldirs = array();
        foreach($this->modules as $name => $module) {
                $tpldirs = array_merge($tpldirs, $module->get_templates_dirs());
        }
        $this->set('templates_dirs', $tpldirs);
    }

    public function require_lib($name, $version, array $files) {
        // if ('' !== $version) {
            $name .= $version;
        // }
        $path = $this->libs->get($name);
        foreach($files as $filename) {
            require_once $path . DIRECTORY_SEPARATOR . $filename;
        }
    }
}


