<?php

namespace micromachine;

class micromachine {

    static function app(Config $conf, $cache_key=null) {
        // On regarde si on a un context basé sur la même conf
        // sauvegardé dans le cache
        if (null != $cache_key) {
            if (apc_exists('micromachine_key')) {
                $mmkey = apc_fetch('micromachine_key');
                if ($mmkey === $cache_key) {
                    $context = apc_fetch('micromachine_context');
                    $context->set('loading_mode','APC');
                    return new self($context);
                }
            }
        }

        return self::app_config_file($conf, $cache_key);
    }

    static function app_config_file(Config $conf, $cache_key=null) {


        // On utilise PHP-Router pour gérer les routes
        // on crée un objet routeur qu'on va faire passer à la config
        // la config va trouver les routes dans chaque module


        $router = new \AltoRouter();
        $router->setBasePath($conf->get_default('base_path', ''));

        $conf->set_routes($router);




        // On crée le contexte contenant toutes les
        // informations créées pour le moment

        $context = new Context(array(
            'conf' => $conf         // App configuration
          , 'router' => $router     // AltoRouter router
          , 'loading_mode' => 'config file'
        ));

        define ('MM_RUNTIME', true);

        // 5. Initialisation des modules

        $context->init_modules();

        // mise en cache

        // rx(v(arw(array(new Ar(array()),new Ar(array()), new Ar(array()))))->export(true));

        r($context->export(true));



        apc_add('micromachine_context', $context);

        // return $handler;
        return new self($context);


    }

    public $_context;

    public function __construct($context) {
        $this->_context = $context;
    }

    public function process() {

        $router = $this->_context->router;

        $_route = $router->match();
        if($_route === false) {
            rx('@todo self::handle_404()');
        }
        else {
            $route = arw($_route);
        }

        // création de l'objet request à l'aide de phptools après le
        // match de route au cas où on veuille détruire les
        // superglobals
        $request = new \micromachine\WebRequest($this->_context->conf->get_default('destroySuperglobals', false));

        // On récupère le handler de la route choisie
        $handler = $route->target;

        // on charge l'objet qui gère la session
        //@todo permettre à la config de définir une autre classe
        $session = new SessionHandler;

        $this->_context->import(array(
            'request' => $request,
            'route' => $route,
            'session' => $session
        ));

        $this->_context->fire('handler_release');

        $handler->set_context($this->_context);

        $handler->process();

    }

}
