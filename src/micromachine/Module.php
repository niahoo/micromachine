<?php

namespace micromachine;

class Module {

    static function load($class,$context) {
        $rc = new \ReflectionClass($class);
        if ($rc->hasMethod('init')) {
            $class::init($context);
        }
        if ($rc->hasMethod('map_routes')) {
            $class::map_routes($context->get('router'),$context);
        } elseif (is_file(mkpath(dirname($rc->getFileName()),'routes.php'))){
            $router = $context->get('router');
            include mkpath(dirname($rc->getFileName()),'routes.php');
        }
        $methods = $rc->getMethods();
        foreach ($methods as $m) {
            if (preg_match('/^event_(.*)/', $m->name, $matches)) {
                $context->observe($matches[1],array($class,$m->name)); // Must be a static function
            }
        }
    }

    static function get_templates_dirs($class) {
        $rc = new \ReflectionClass($class);
        $base_dir = dirname($rc->getFileName());
        $gather = self::glob(mkpath($base_dir, "templates", "*"), GLOB_ONLYDIR);
        $gather = array_merge($gather, self::glob(mkpath($base_dir, "templates", "*", "*"), GLOB_ONLYDIR));
        $tpldir = mkpath($base_dir, "templates");
        if (is_dir($tpldir))
            array_unshift($gather, $tpldir);
        return $gather;
    }

    static function glob($mask, $opts=0) {
        $result = glob($mask,$opts);
        return $result === false ? array() : $result;
    }
}
