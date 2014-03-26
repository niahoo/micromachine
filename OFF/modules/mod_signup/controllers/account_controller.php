<?php

class Account_Controller {





    public function _before_action($context, $action, $resp_stack) {

        if(!$context->auth->is_logged()) {

            $context->session->set_location($context);
            return Redirect(302, $context->router->generate('mod_signup_login'));
        }
        else {
            return true;
        }
    }


    public function index($context) {
        $html =  $context->render('account_home.html');
        return $html;
    }

}
