<?php

namespace micromachine;

class Module extends Ar {

    static function load($module_dir, $conf) {

        $module = new self(array('conf' => $conf));
        /**
         * on var regarder si les modules sont trouvables dans le
         * app_root de la conf, et sinon dans les modules de base
         * de micromachine
         */

		$try1 = $conf->app_root . '/modules/' . $module_dir;
		$try2 = dirname(dirname(dirname(__FILE__))) . '/modules/' . $module_dir;

        if(is_dir($try1)) {
            $module->set('dir', $try1);
        }
        elseif(is_dir($try2)) {
            $module->set('dir', $try2);
        }
        else {
            throw new \InvalidArgumentException("$module_dir module dir not found");
        }

        $module->set('name', basename($module_dir));

        $module->check_main_file();
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

    public function file($path) {
        return $this->dir . $path;
    }

    //@todo refactor avec controllers, models
    public function get_controllers() {
        $base_path = $this->dir;
        $search_mask = $base_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . '*.php';
        $gather = glob($search_mask);
        return self::filenames_to_keys($gather);
    }

    public function get_models() {
        $base_path = $this->dir;
        $search_mask = $base_path . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . '*.php';
        $gather = glob($search_mask);
        return self::filenames_to_keys($gather);
    }

    public function get_exceptions() {
        $base_path = $this->dir;
        $search_mask = $base_path . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR . '*.php';
        $gather = glob($search_mask);
        return self::filenames_to_keys($gather);
    }

    public function get_libs() {
        $base_path = $this->dir;
        $search_mask = $base_path . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '*';
        $gather = glob($search_mask);
        return self::filenames_to_keys($gather, $with_ext=true);
    }

    public function get_templates_dirs() {
        $gather = glob($this->dir . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR);
        $gather = array_merge($gather, glob($this->dir . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "*" . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR));
        if (is_dir($this->dir . DIRECTORY_SEPARATOR . "templates"))
            array_unshift($gather, $this->dir . DIRECTORY_SEPARATOR . "templates");
        return $gather;
    }

    public static function filenames_to_keys(array $files_paths, $with_ext=false, $dirname_levels=0) {
        $filenames_to_keys = array();
        foreach ($files_paths as $path) {
            $key = '';
            $build_path = $path;
            for ($i = 0; $i < $dirname_levels; $i++) {
                $build_path = dirname($build_path);
                $key = pathinfo($build_path, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR . $key;
            }
            $key .= pathinfo($path, PATHINFO_FILENAME);
            if (true == $with_ext && pathinfo($path, PATHINFO_EXTENSION) != '')
                $key .= '.' . pathinfo($path, PATHINFO_EXTENSION);
            $filenames_to_keys[$key] = $path;
        }
        return $filenames_to_keys;
    }

    public function has_main_file() {
        return is_file($this->main_file_path());
    }

    public function check_main_file() {
        $this->set('has_main_file', $this->has_main_file());
    }

    public function main_file_path() {
        return $this->dir . DIRECTORY_SEPARATOR . $this->name . '.php';
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
}
