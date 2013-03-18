<?php

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

class mod_cache {

    public static function init($context) {
        $adapter = $context->conf->get_default('cache.adapter', false);
        if (!$adapter) {
            $dir = mkpath(APPROOT, 'cache', 'mod_cache');
            if (!is_dir($dir)) { mkdir($dir,0640,true); }
            $adapter = new LocalAdapter($dir);
        }
        $context->set('cache', new self($adapter));
    }

    public $adapter;

    public function __construct($adapter) {
        $this->adapter = $adapter;
    }

    //@var $max_age max file age in seconds
    public function get($name, $max_age) {
        if ($this->adapter->exists($name) && $this->adapter->mtime($name) + $max_age > time()) {
            return $this->adapter->read($name);
        }
        else {
            throw new mod_cache_not_found_exception("Cache item $name not found");
        }
    }

    public function get_or_create($name, $max_age, $create_fun) {
        try {
            return $this->get($name, $max_age);
        } catch (mod_cache_not_found_exception $e) {
            $content = $create_fun();
            $this->adapter->write($name, $content);
            return $content;
        }
    }
}

class mod_cache_not_found_exception extends RuntimeException {

}
