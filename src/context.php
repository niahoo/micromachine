<?php

namespace micromachine;

class Context extends Ar {
    public function require_lib($name, $version, array $files) {
        return $this->conf->require_lib($name, $version, $files);
    }

    public function require_part($type, $_name) {
        $name = strtolower($_name);
        switch($type) {
            case 'controller':
                return $this->conf->require_controller($name);
                break;
            case 'module':
                return $this->conf->require_module_file($name);
                break;
            case 'model':
                return $this->conf->require_model($name);
                break;
            case 'exception':
                return $this->conf->require_exception($name);
                break;
            default: throw new \InvalidArgumentException("Bad type '$type'");
        }
    }

    public function init_modules() {
        $mods_to_init = $this->conf->mods_to_init;
        foreach($mods_to_init as $module) {
            $this->require_part('module', $module);
            $module::init($this);
        }

    }

    public function __call($method, $args)
    {
        $fun = $this->$method; //@todo ici variable inutilisée
        return call_user_func_array($this->$method, $args);
    }

    public function fire($event_name, $data=null) {
        foreach ($this->conf->modules as $module) {
            if(in_array($event_name, $module->events)) {
                  $this->conf->require_module_file($module->name);
                  return call_user_func('\\' . "{$module->name}::event_$event_name", $this, $data);
            }
        }
    }

    public function _gen_ex($className, $message, $code=0, $abort_ifndef=false) {
        // Cette fonction permet de créer des exceptions à la volée
        // /!\ Un fichier contenant la définition de l'exception
        // est créé
        if (class_exists($className)) {
            return new $className($message, $code);
        }
        elseif ($abort_ifndef) {
            throw new \InvalidArgumentException("Aborted : Exception $className not found");
        }
        elseif ($this->require_part('exception', $className)) {
            return new $className($message, $code);
        }
        else {
            // création du fichier
            $create_in = $this->conf->get_default('exceptions.dir', mkpath($this->conf->app_root,'exceptions'));
            if (!is_dir($create_in)) {
                if (!mkdir($create_in, 0777)) { //@todo HYPER IMPORTANT passer à 0770
                    exit("Can\'t create dir $create_in");
                }
            }

            $write_in = mkpath($create_in,strtolower($className).'.php');
            $writes = file_put_contents($write_in, CodeGen::build_exception($className));

            if (false === $writes) {
                exit('Vous ne devriez pas trop jouer avec ça ...');
            }
            else {
                chmod($write_in, 0777); //@todo HYPER IMPORTANT passer à 0660
                require_once $write_in;
                return $this->_gen_ex($className, $message, $code, true);
            }
        }
    }
}
