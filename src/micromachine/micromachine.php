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

        // Mise en place du logger -- on utilise monolog

        $mm_log = new \Monolog\Logger('mm_log');
        $log_handlers = $conf->get_default('log.main.handlers', array(new \Monolog\Handler\StreamHandler('php://stderr')));
        foreach ($log_handlers as $h) {
            $mm_log->pushHandler($h);
        }


        // On crée le contexte contenant toutes les
        // informations créées pour le moment

        $context = new Context(array(
            'conf' => $conf         // App configuration
          , 'router' => $router     // AltoRouter router
          , 'loading_mode' => 'config file'
          , 'log' => $mm_log
        ));

        define ('MM_RUNTIME', true);

        // 5. Initialisation des modules
        $context->log->addDebug('Modules Initialisation');
        $context->init_modules();
        $context->log->addDebug('Modules Initialisation Done');
        // return $handler;
        return new self($context);


    }

    public $_context;
    private $noroute_handler = null;

    public function __construct($context) {
        $this->_context = $context;
    }

    public function set_no_route_handler (/* callable */ $fun) {
        $this->noroute_handler = $fun;
    }

    public function process() {

        $this->_context->log->addDebug($_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' Handler processing request');
        // création de l'objet request à l'aide de phptools après le
        // match de route au cas où on veuille détruire les
        // superglobals
        $request = new \micromachine\WebRequest($this->_context->conf->get_default('destroySuperglobals', false));


        // on charge l'objet qui gère la session
        //@todo permettre à la config de définir une autre classe
        $session = new SessionHandler;

        $router = $this->_context->router;


        $this->_context->import(array(
            'request' => $request,
            'session' => $session
        ));

        // rx($_SERVER['REQUEST_METHOD'],sys_get_temp_dir());

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

            return $handler->process();
        }

    }

    public function handle_no_route_found() {
        if ($this->noroute_handler !== null) {
            $fun = $this->noroute_handler;
            return $fun($this->_context);
        }
        else {

            //@todo check si le module errorpages est chargé et on le
            // charge uniquement s'il ne l'est pas
            $this->_context->conf->load_module('mod_errorpage');
            $this->_context->init_module('mod_errorpage');

            $response = $this->_context->errorpages->render(404);
            $response->send_headers();
            $response->output();
        }


    }

}
