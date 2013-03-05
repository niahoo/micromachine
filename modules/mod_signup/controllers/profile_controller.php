<?php

class Profile_Controller {


    public function _before_action($context, $action, $resp_stack) {

        if(!$context->auth->is_logged()) {

            $context->session->set_location($context);
            return Redirect(302, $context->router->generate('mod_signup_login'));
        }
        else {
            return true;
        }
    }


    public function new_profile($context) {
        $current_count = count($context->auth->account()->ownProfile);
        if ($current_count >= $context->auth->max_profiles()) {
            return $context->render('max_profile_error.html');
        }
        $previous_values = $context->session->get_default('new_profile_post', array());

        $tpl_vars = array(
            'form_url' => $context->router->generate('mod_signup_new_profile_process') ,
            'previous' => $previous_values
        );
        $html =  $context->render('new_profile.html', $tpl_vars);
        return $html;
    }

    public function process_new($context) {

        $post_profile = $context->request->post('profile');
        if(!is_array($post_profile)) {
            $post_profile = array();
        }
        // sauvegarde du POST dans la session au cas ou il y aurait
        // des erreurs, pour ne pas retaper tout le formulaire
        $context->session->set('new_profile_post', $post_profile);

        $account = $context->auth->account();

        $current_count = count($account->ownProfile);
        if ($current_count >= $context->auth->max_profiles()) {
            return $context->render('max_profile_error.html');
        }
        if(0 == $current_count) {

        }
        $context->require_part('model', 'Model_Profile');
        $profile = R::dispense('profile');
        $auth_fields = $context->conf->get_default('signup.profiles_auth_fields', null);

        $profile->import($post_profile, $auth_fields);
        $profile->is_default = ($current_count > 0) ? false : true;
        try {
            R::store($profile);
        }
        catch(Exception $e) {
            $context->session->appendflash('errors', $e->getMessage());
            return $this->new_profile($context);
        }

        $account->ownProfile[] = $profile;

        R::store($account);

        $context->session->appendflash('validations', _('Profil créé'));
        $redirect_to = $context->session->previous_location(
            $context,
            $context->router->generate('user_account') // default
        );
        return Redirect(302, $redirect_to);
    }
}
