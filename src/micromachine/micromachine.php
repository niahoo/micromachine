<?php

namespace micromachine;

class micromachine {

    static function app(Config $conf) {

        // On utilise PHP-Router pour gérer les routes
        // on crée un objet routeur qu'on va faire passer à la config
        // la config va trouver les routes dans chaque module

        public $root = dirname(dirname(dirname(__FILE__)))

        $router = new \Router();
        $router->setBasePath($conf->get_default('base_path', ''));

        $conf->set_routes($router);

        $route = $router->matchCurrentRequest();
        if($route === false) {
            rx('@todo self::handle_404()');
        }

        // création de l'objet request à l'aide de phptools

        $request = new \WebRequest($conf->get_default('destroySuperglobals', false));

        // On récupère le handler de la route choisie
        $handler = $route->getTarget();
        // on charge l'objet qui gère la session
        //@todo permettre à la config de définir une autre classe

        $session = new Default_Session_Handler;



        // On crée le contexte contenant toutes les
        // informations créées pour le moment

        $context = new Context(array(
            'conf' => $conf         // App configuration
          , 'request' => $request   // Webrequest object
          , 'router' => $router     // Php-Router router
          , 'route' => $route       // Php-Router matched route
          , 'session' => $session   // session handler
        ));

        define ('MM_RUNTIME', true);

        // 5. Initialisation des modules

        $context->init_modules();

        $context->fire('before_handler_release');

        $handler->set_context($context);

        return $handler;



    }

}
