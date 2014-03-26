<?php

class mod_signup {

    // STATIC API

    static $signup_salt = null;
    static $hash_identity_fun = null;

    // roles constants

    const role_admin = 1;

    public static function init(\micromachine\Context $context) {
        self::$signup_salt = $context->conf->get('signup.salt');
        self::$hash_identity_fun = $context->conf->get('signup.identity_hash');
    }

    public static function event_handler_release($context) {
        $context->set('auth', new self($context));
        $context->require_part('model','Model_Account');
        $context->require_part('model','Model_Identity');
    }

    public static function signup_salt() {
        if (is_null(self::$signup_salt)) {
            throw new Exception ('mod_signup not initialized');
        }
        return self::$signup_salt;
    }

    public static function hash_identity_fun() {
        if (is_null(self::$hash_identity_fun)) {
            throw new Exception ('mod_signup not initialized');
        }
        return self::$hash_identity_fun;
    }

    public static function hash_pass($password) {
        $salted = self::signup_salt() . $password;
        return hash('sha1', $salted) . hash('crc32', $salted);
    }

    public static function check_email_is_used($email, $identity_type, $context) {
        // pour voir si un email est utilisé, on regarde dans la table
        // doit le chercher dans chaque table
        $methods = $context->conf->get_default('signup.methods', array('email'));
        $doubles = R::find('identity',
            'email = :email and identity_type = :identity_type',
             array('email' => $email, 'identity_type' => $identity_type));
        if(count($doubles) !== 0) {
            return true;
        }
        return false;
    }

    public static function identity_type($id=null) {
        $types = array(
            'email' => 1
          , 'facebook' => 2
        );
        if('' !== $id) {
            if(isset($types[$id])) {
                return $types[$id];
            }
            else {
                throw new InvalidArgumentException('bad identity type');
            }
        }
        else {
            return $types;
        }
    }

    public static function check_password($password, $context) {
        // ici on regarde si la config propose une fonction pour
        // checker.
        // si ce n'est pas le cas, on a une fonction par défaut
        // la fonction doit accepter 1 paramètre, le password
        // la fonction doit renvoyer array($ok,$errors) ou
        //  $ok = true | false
        //  $errors = array("description de l'erreur") | array()
        $checkfun = $context->conf->get_default(
            'signup.password_check',
            function ($pass) {
                if(strlen($pass) < 5) {
                    return array(false, array(_("Le mot de passe doit faire 5 caractères au minimum.")));
                }
                else {
                    return array(true, array());
                }
            });

        return $checkfun($password);
    }

    public static function hash_identity(Model_Identity $identity) {
        // récupère une fonction à 1 paramètre ($model_identity)
        // depuis la config et lui passe $identity pour en récupérer
        // le hash

        $hash_fun = self::hash_identity_fun();
        return $hash_fun($identity->unbox());
    }

    public static function find_identity_email_by_password($email, $pass) {
        $hash = self::hash_pass($pass);
        $possible_bean = R::findOne('identity', 'email = ? and pass_hash = ? LIMIT 1',
            array($email, $hash));
        return array(! is_null($possible_bean), $possible_bean);
    }

    public static function role_key($name) {
        $key = "role_$name";
        $rc = new ReflectionClass('mod_signup');
        return arw($rc->getConstants())->get_default($key, 0);
    }

    // CONTEXT HELPERS API

    private $context;

    public function __construct(\micromachine\Context $context) {
        $this->context = $context;
        $this->session = $context->session;
    }

    public function is_logged() {
        return $this->session->get_default('is_logged', false);
    }

    public function set_logged($account_id) {
        $this->session->set('is_logged', true);
        $this->session->set('account_id', $account_id);
    }

    public function set_not_logged() {
        $this->session->remove('is_logged');
        $this->session->remove('account_id');
    }

    public function count() {
        $base_count = $this->session->get_default('count',0);
        $count = $base_count + 1;
        $this->session->set('count',$count);
        return $count;
    }

    public function max_profiles() {
        return intval($this->context->conf->get_default('signup.max_profiles', 99));
    }

    public function id() {
        if(!$this->is_logged()) {
            throw $this->context->_gen_ex('NotLoggedException','Not logged');
        }
        return $this->session->account_id;
    }

    public function account() {
        // @todo optimisation, charger le compte une seule fois et en session ?
        return R::load('account', $this->id());
    }

    public function require_login() {
        $this->context->session->set_location($this->context);
        return Redirect(302, $this->context->router->generate('mod_signup_login'));
    }

    public function require_profile() {
        $this->context->session->set_location($this->context);
        return Redirect(302, $this->context->router->generate('mod_signup_new_profile'));
    }

    public function role($name) {
        // check si l'user a le role demandé
        $i = self::role_key($name);
        return (intval($this->account()->userroles) & $i) == $i;

    }
}
