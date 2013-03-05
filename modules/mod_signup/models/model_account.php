<?php

class Model_Account extends RedBean_SimpleModel {

    public function update() {
        // ici on check qu'on a bien au moins une identity, au cas ou
        // on voudrait par exemple dissocier de facebook un compte qui
        // n'aurait que facebook comme connexion
        if (0 === count($this->ownIdentity)) {
            // @todo créer des exception class spécifiques
            throw new Exception('An account must have at least one identity');
        }
    }

    public function get_email_identities() {
        return R::find('identity', 'account_id = ? AND identity_type = ?',
            array($this->id, mod_signup::identity_type('email')));
    }

    public function has_profile() {
        return count($this->ownProfile);
    }

    public function default_profile() {
        return R::findOne('profile', 'account_id = ? AND is_default = ?', array($this->id, true));
    }

    public function add_role($name) {
        $i = mod_signup::role_key($name);
        $base = intval($this->userroles);
        $this->userroles = $base | $i;
    }

    public function rm_role($name) {
        $i = mod_signup::role_key($name);
        $base = intval($this->userroles);
        $this->userroles = $base &~ $i;
    }
}

