<?php 
class password_controller {


    public function _before_action($context, $action, $resp_stack) {
        
        if(!$context->auth->is_logged()) {
            return Redirect(302, $context->router->generate('mod_signup_login'));
        }
        else {
            $context->require_part('model', 'Model_Account');
            $context->require_part('model', 'Model_Identity');
            return true;
        }
    }

    public function process_change($context) {


        // ici on va changer le password pour l'identity email.
        //@todo à modifier si on implémente la possibilité d'avoir
        // plusieurs emailidentity par compte
        // on réalise les actions dans l'odre du moindre coût
        // check si new pass et confirm sont identiques, check si
        // le new pass est valide selon la config, check si l'user
        // donne son bon ancien mot de passe.
        //



        $to_account_page = Redirect(302, $context->router->generate('user_account'));

        // Récup des variables
        $old_pass = $context->request->post('signup_current_password');
        $new_pass = $context->request->post('signup_new_password');        
        $con_pass = $context->request->post('signup_confirm_password');

        // Check si les deux nouveaux correspondent
        if ($new_pass !== $con_pass) {
            $context->session->appendflash('errors', _('Les mots de passe ne correspondent pas.')); 
            return $to_account_page;            
        }

        // Check si le mot de passe est valide selon la config
        list($password_ok, $pass_check_errors) = mod_signup::check_password($new_pass, $context);

        if(!$password_ok) {
            foreach($pass_check_errors as $pass_check_error) {
                $context->session->appendflash('errors', $pass_check_error);     
            }
            return $to_account_page;
        }                



        // load du compte
        $account = R::load('account', $context->session->account_id);

        if (!$account->id) {
            $context->session->appendflash('errors', _('Vous n\'êtes plus connecté(e)')); 
            Redirect(302, $context->router->generate('mod_signup_login'));
        }

        // load de l'identity email
        $identities = $account->get_email_identities();

        if(count($identities) < 1) {
            $context->session->appendflash('errors', _('Vous n\'avez pas de compte nécessitant un mot de passe.')); 
            return $to_account_page;
        }

        $identity = array_pop($identities);
        unset($identities);
        if(!$identity->match_password($old_pass)) {
            $context->session->appendflash('errors', _('Ancien mot de passe incorrect')); 
            return $to_account_page;
        }

        // C'est ok

        $identity->pass_hash = mod_signup::hash_pass($new_pass);
        R::store($identity);
        $context->session->appendflash('validations', 'Mot de passe modifié avec succès');
        return $to_account_page;
    }
}