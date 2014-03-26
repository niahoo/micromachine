<?php

class Login_Controller {

    public function _before_action($context, $action, $resp_stack) {
    //   @todo que faire si on arrive sur le login et qu'on est connecté ?
        $context->require_part('model', 'Model_Identity');
    }


    public function index($context) {
       $html =  $context->render('login_page.html');
       return $html;
    }

    public function process_login($context) {
        $email = $context->request->post('login_email');
        $pass = $context->request->post('login_password');

        list($found, $identity) = mod_signup::find_identity_email_by_password($email, $pass);

        if(!$found) {
            $context->session->appendflash('errors', _('Mauvais mot de passe ou email non enregistré.'));
            return Redirect(302, $context->router->generate('mod_signup_login'));
        }
        else {

            // On check si le hash des données user correspond toujours
            if(!$identity->check_data_signature()) {
                $context->session->appendflash('errors', _('Vos données sont corrompues, contacter un administrateur.'));
                //@todo envoyer un rapport d'erreur à l'admin
            }

            $context->auth->set_logged($identity->account->id);
            $context->session->appendflash('validations', _('Connexion réussie'));
            //@todo redirect à la page précédente si l'info est disponible
            return Redirect(302,$context->session->previous_location($context));
        }


    }

    public function logout($context) {
        $context->auth->set_not_logged();
        $context->session->appendflash('validations', _('Déconnexion réussie'));
        return Redirect(302, ($context->conf->base_path?:'/'));
    }
}
