<?php

namespace micromachine;

class ControllerHandler {

    private $controller;
    private $action;

    // le context est public pour les cas où on aurait besoin d'y
    // jetter un coup d'oeil dans un cadre de développement/débug
    public $_context;

    public function __construct($controller, $action='index') {
        $this->controller = $controller;
        $this->action = $action;
    }

    public function set_context(Context $context) {
        $this->_context = $context;
    }

    public function process() {
        // On va simplement appeller le controlleur et son action
        // on va lui passer le contexte en premier paramètre
        // puis on va lui passer les paramètres du path comme
        // paramètres supplémentaires
        $args = array_merge(
            array($this->_context),
            $this->_context->route->parameters
        );

        $this->_context->require_part('controller', $this->controller);
        $controller = new $this->controller;

        // CALL STAGES BEFORE ACTION

        $stages = array(
            '_before_action'
        );

        $_response = null;
        $responses_stack = array();

        foreach($stages as $stage) {
            $_response = $this->call_stage($controller,$stage,$this->action, $responses_stack);

            // Si on obtient un objet de type Response, on stoppe

            if($_response instanceof Response) {
               return $this->output($_response); // ! RETURN
            }
            else {
                $responses_stack[$stage] = $_response;
            }
        }


        // CALL ACTION
        if(! method_exists($controller, $this->action)) {
            throw new \InvalidArgumentException('Action '.$this->action.' does not exists in controller '. get_class($controller));
        }
        $action_response = call_user_func_array(array($controller, $this->action), $args);

        if($action_response instanceof Response) {
           return $this->output($action_response); // ! RETURN
        }


        if(is_null($action_response)) {
            // si la réponse est nulle, on ne fait rien tout simplement
            //@todo : faire autre chose, envoyer un 204 ?
            $class = get_class($controller);
            r("@todo : empty $class return");
            return; // ! RETURN
        }
        elseif(is_string($action_response)) {
            $response = new Response($action_response, $headers=array());
            return $this->output($response); // ! RETURN
        }


    }

    public function call_stage ($controller,$stage,$action, $stack) {

        if(method_exists($controller, $stage)) {
            return call_user_func(array($controller, $stage), $this->_context, $action, $stack);
        }
        return null;

    }


    public function output($response) {
        $response->send_headers();
        $response->output();
        return true;
    }

}
