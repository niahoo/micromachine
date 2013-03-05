<?php

class Model_Identity extends RedBean_SimpleModel {
    public function generate_salt() {
    	if (!is_null($this->salt)) {
            // @todo créer des exception class spécifiques
    		throw new Exception('This bean has already a salt');
    	}

    	$salt = self::random_salt();
    	$this->bean->salt = $salt;

    }

    private static function random_salt() {
    	return hash('crc32', mt_rand(0,1000).microtime());
    }

    public function update() {
    	// ici on va checker : que la salt n'est pas vide, qu'elle a
    	// donc été générée lors de la création du bean
    	// et on va ensuite mettre à jour le hash de toutes les infos
        if (is_null($this->bean->salt)) {
            throw new Exception('This bean has no salt');
        }
        $this->bean->infos_hash = $this->get_hash_infos();
    }

    public function get_hash_infos() {
        // ici on hash toutes les informations du bean en une seule
        // chaine, avec un salt

        $hash = mod_signup::hash_identity($this);
        return $hash;

    }

    public function check_data_signature() {
        return $this->get_hash_infos() === $this->infos_hash;
    }

    public function match_password($pass) {
        $match = mod_signup::hash_pass($pass) === $this->pass_hash;
        return $match;
    }
}

