<?php

class Signup_Controller {

    



    public function _before_action($context) {
        //::
    }


    public function index($context) {
       $html =  $context->render('signup.html');
       return $html;
    }   

    public function process_email($context) {
        // check si une indentity de type email existe déjà

        $context->require_part('model', 'Model_Identity');


        $email = $context->request->post('signup_email');
        $password = $context->request->post('signup_password');
        $pconfirm = $context->request->post('signup_confirm');

        $process_ok = true;

        if($pconfirm !== $password){ 
            $context->session->appendflash('errors', 'Les mots de passe ne correspondent pas');     
            $process_ok = false;       
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ 
            $context->session->appendflash('errors', 'Email non valide');     
            $process_ok = false;       
        }

        list($password_ok, $pass_check_errors) = mod_signup::check_password($password, $context);

        if(!$password_ok) {
            foreach($pass_check_errors as $pass_check_error) {
                $context->session->appendflash('errors', $pass_check_error);     
            }
            $process_ok = false;       
        }
        
        if(mod_signup::check_email_is_used($email, mod_signup::identity_type('email'), $context)) {
            $context->session->appendflash('errors', _('Cet email est déjà utilisé'));            
            $process_ok = false;
        }

        if(false === $process_ok) {
            return Redirect(302, $context->router->generate('mod_signup_signup'));
        }
        else {
            $identity = R::dispense('identity');
            $identity->identity_type = mod_signup::identity_type('email');
            $identity->email = $email;
            $identity->email_validated = false;
            $identity->pass_hash = mod_signup::hash_pass($password);
            $identity->generate_salt();




            //@todo ici on a vérifié qu'une identity de type email
            // n'existait pas déjà, mais l'email peut exister sur
            // un login facebook
            // dans ce cas on lui dit qu'on oblige à se connecter avec
            // son compte facebook et à créer l'identity depuis son 
            // compte. (il a juste à donner un mot de passe)
            // sinon créer le compte

            $account_data = array(
                'email' => $identity->email
              , 'creation_date' => R::isoDate()
            );

            $account = R::dispense('account');
            $account->import($account_data);

            $account->ownIdentity[] = $identity;

            R::store($account);

            $context->session->appendflash('validations', _('Compte créé avec succès'));
            $context->auth->set_logged($account->id);
            $redirect_to = $context->session->previous_location(
                $context,
                $context->router->generate('user_account') // default
            ); 
            return Redirect(302, $redirect_to);
        }
    }
}

