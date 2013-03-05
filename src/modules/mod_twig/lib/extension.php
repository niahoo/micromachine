<?php

class Mod_Twig_Extension extends Twig_Extension {

    private $context;

    public function __construct($context) {
        $this->context = $context;
    }

    // INTERFACE

    // public function initRuntime(\Twig_Environment $twig) {}

    // public function getTokenParsers () {}
    // public function getNodeVisitors () {}
    // public function getFilters () {}
    // public function getTests () {}
    public function getFunctions () {
        return array(
            'r' => new Twig_Function_Method($this, 'fun_r')
          , 'url' => new Twig_Function_Method($this, 'fun_url')
          , 'rawurl' => new Twig_Function_Method($this, 'fun_rawurl')
          , 'flash' => new Twig_Function_Method($this, 'fun_flash')
          , 'checked' => new Twig_Function_Method($this, 'fun_checked')
        );
    }
    // public function getOperators () {}
    // public function getGlobals () {}
    public function getName () {
        return 'mod_twig';
    }

    // FUNS

    public function fun_r($var, $label='', $step=0) {
       r($var,$label,$step+3);
    }

    // crée une URL à partir du Router
    public function fun_url($name, array $args=array()) {
        return $this->context->router->generate($name, $args);
    }

    // ajoute le chemin donné au base path
    public function fun_rawurl($path, array $args=array()) {
        $base_path = $this->context->conf->get_default('base_path', '');
        $lastchar = substr($base_path, -1);
        $sep = '';
        if ('/' != $lastchar && '/' != $path[0]) {
            $sep = '/';
        }
        return $base_path . $sep . $path;
    }


    public function fun_flash($key) {
        return $this->context->session->flash($key);
    }

    public function fun_checked($value) {
        //@todo faire une comparaison boléenne plus stricte que if(variable)
        if($value) {
            return 'checked';
        }
        else {
            return '';
        }
    }

}

