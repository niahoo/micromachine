<?php

namespace micromachine;

class micromachine {

    public $context;
    private $noroute_handler = null;

    static function app(Config $conf) {
        $router = new \AltoRouter();
        $router->setBasePath($conf->get_default('base_path', ''));
        $context = Context::create(array(
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
        $request = new \micromachine\WebRequest($this->context->conf->get_default('destroySuperglobals', false));
        $router = $this->context->router;
        $this->context->import(array(
            'request' => $request
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
