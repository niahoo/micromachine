<?php

namespace micromachine;

class micromachine {

    static function app(Config $conf) {

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
        ));

        define ('MM_RUNTIME', true);

        // 5. Initialisation des modules

        $context->init_modules();

        $context->fire('before_context_release');

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

        $this->_context->fire('before_handler_release');

        $handler->set_context($this->_context);

        $handler->process();

    }

}
