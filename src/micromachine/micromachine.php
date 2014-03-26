<?php

namespace micromachine;

class micromachine {

    public $context;
    private $noroute_handler = null;

    static function app(Config $conf) {
        $router = new \AltoRouter();
        $router->setBasePath($conf->get_default('base_path', ''));
        $conf->set_routes($router);
        $context = new Context(array(
            'conf' => $conf         // App configuration
          , 'router' => $router     // AltoRouter router
        ));
        define ('MM_RUNTIME', true);
        $context->init_modules();
        return new self($context);
    }

    public function __construct($context) {
        $this->context = $context;
    }

    public function set_no_route_handler (/* callable */ $fun) {
        $this->noroute_handler = $fun;
    }

    public function process() {
        $this->context->log->addDebug($_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' Handler processing request');
        $request = new \micromachine\WebRequest($this->context->conf->get_default('destroySuperglobals', false));
        $session = new SessionHandler; //@todo permettre à la config de définir une autre classe
        $router = $this->context->router;
        $this->context->import(array(
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
            $this->context->set('route', $route);
            $this->context->fire('handler_release');
            $handler->setcontext($this->context);
            return $handler->process();
        }
    }

    public function handle_no_route_found() {
        if ($this->noroute_handler !== null) {
            $fun = $this->noroute_handler;
            return $fun($this->context);
        }
        else {
            $response = Response('404 Error', array(), 404);
            $response->send_headers();
            $response->output();
        }
    }
}
