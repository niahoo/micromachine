<?php

namespace micromachine;

class SessionHandler extends Ar {

    const session_sub = 'mm';

    public function __construct() {
        ensure_session_start();
        if(!isset($_SESSION[self::session_sub])) {
            $_SESSION[self::session_sub] = array();
        }
        $this->state = & $_SESSION[self::session_sub];
        $this->__CLASS__ = get_class($this);
    }

    public function addflash($key, $value) {
    	$this->state['__FLASH__'][$key] = $value;
    }

    public function appendflash($key, $value) {
    	if(!isset($this->state['__FLASH__'][$key])) {
    		$this->state['__FLASH__'][$key] = array();
    	}
    	$this->state['__FLASH__'][$key][] = $value;
    }

    public function flash($key) {
        if(!isset($this->state['__FLASH__'][$key])) {
            return array();
        }
        else {
            $flashval = $this->state['__FLASH__'][$key];
            unset($this->state['__FLASH__'][$key]);
            return $flashval;
        }
    }

    public function set_location(Context $context) {
        // ici on garde en mémoire les URL parcourues par
        // l'utilisateur
        // on enregistre que les requêtes GET
        if('GET' === $context->request->method()) {
            $this->push_to('locations_stack',  $_SERVER['REQUEST_URI']);
        }
        //@todo s'assurer que la stack n'exède pas X items pour ne pas plomber la session
        // je pense à un système de cleanup. Actuellement la session
        // ne garde pas le context. Au démarrage de mm, on peut donner
        // à la session un objet cleaner, une classe qui répondrait
        // uniquement à une méthode session_cleanup
        // dans la conf d'une appli, on peut spécifier une autre classe
        // perso qui prendrait des mesures choisies par l'utilisateur
        // lors de cet event.
        return true;
    }
    public function previous_location($context=null, $_default=null) {
        if(is_null($_default)) {
            if(!is_null($context)) {
                // on évite la chaine vide en la remplaçant par '/'
                // le cas échéant
                $default = ($context->conf->base_path ?: '/');

            }
            else {
                $default = '/';
            }
        }
        else {
            $default = $_default;
        }
        return $this->pop_from('locations_stack', $default);
    }

    public function __get($key) {
        return $this->get_default($key, null);
    }
}

