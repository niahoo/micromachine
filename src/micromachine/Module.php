<?php

namespace micromachine;

class Module extends Ar {

    static function load($module_name, $conf, $dir) {

        $module = new self(array('conf' => $conf));
        /**
         * on var regarder si les modules sont trouvables dans le
         * app_root de la conf, et sinon dans les modules de base
         * de micromachine
         */

        $module->set('dir', $dir);
        $module->set('name', $module_name);

        $module->set('has_init_method', false);
        $module->set('events',array());
        if($module->has_main_file()) {
            $module->set_events();
        }

        return $module;
    }

    public function set_routes($router) {
        if (is_file($this->file(DIRECTORY_SEPARATOR . 'routes.php'))) {
            include $this->file(DIRECTORY_SEPARATOR . 'routes.php');
        }
    }

    public function get_templates_dirs() {
        $gather = self::glob(mkpath($this->dir, "templates", "*"), GLOB_ONLYDIR);
        $gather = array_merge($gather, self::glob(mkpath($this->dir, "templates", "*", "*"), GLOB_ONLYDIR));
        $tpldir = mkpath($this->dir, "templates");
        if (is_dir($tpldir))
            array_unshift($gather, $tpldir);
        return $gather;
    }

    public function load_methods() {
        $methods = $this->get_default('methods', false);
        if(false === $methods) {
            // memoization
            require_once $this->main_file_path();
            $rc = new \ReflectionClass($this->name);
            $methods = $rc->getMethods();
            $this->set('methods', $methods);
        }
        return $methods;
    }

    private function set_events() {
        $methods = $this->load_methods();
        $events = array();
        foreach ($methods as $m) {
            if ('init' === $m->name)
                $this->set('has_init_method', true);
            $matches = array();
            if (preg_match('/^event_(.*)/', $m->name, $matches)) {
                $events[] = $matches[1];
            }
        }
        $this->set('events', $events);
    }

    // fonction utile qui zappe les erreurs de path sur certains
    // hébergeurs
    private static function glob($mask, $opts=0) {
        $result = glob($mask,$opts);
        return $result === false ? array() : $result;
    }
}
