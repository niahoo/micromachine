<?php

namespace micromachine;

class micromachine {

    static function app(Config $conf) {
        return self::app_config_file($conf);
    }

    static function app_config_file(Config $conf) {


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

        // return $handler;
        return new self($context);


    }

    public $_context;

    public function __construct($context) {
        $this->_context = $context;
    }

    public function process() {


        // création de l'objet request à l'aide de phptools après le
        // match de route au cas où on veuille détruire les
        // superglobals
        $request = new \micromachine\WebRequest($this->_context->conf->get_default('destroySuperglobals', false));

        // On récupère le handler de la route choisie


        // on charge l'objet qui gère la session
        //@todo permettre à la config de définir une autre classe
        $session = new SessionHandler;

        $router = $this->_context->router;


        $this->_context->import(array(
            'request' => $request,
            'session' => $session
        ));

        $_route = $router->match();
        if($_route === false) {
            return $this->handle_no_route_found();
        }
        else {
            $route = arw($_route);
            $handler = $route->target;

            $this->_context->set('route', $route);
            $this->_context->fire('handler_release');

            $handler->set_context($this->_context);

            $handler->process();
        }





    }

    private function handle_no_route_found() {
        // check si le module errorpages est chargé et on le charge
        // sinon
        $this->_context->conf->load_module('mod_errorpage');
        $this->_context->init_module('mod_errorpage');

        $response = $this->_context->errorpages->render(404);
        $response->send_headers();
        $response->output();


    }

}
