<?php

namespace micromachine;

class Ar {

    protected static $instances = array();
    protected static $ciID = 1000;

    protected $state = array();
    protected $__CLASS__ = __CLASS__;

    public function __construct(array $state) {
       $this->state = $state;
       $this->__CLASS__ = get_class($this);
    }

    public function import(array $state) {
        $this->state = array_merge($this->state, $state);
        return $this;
    }

    protected function new_instance_ID() {
        $id = Ar::$ciID;
        Ar::$ciID += 1;
        return $id;
    }

    public function export($full=false) {
        $this->set('__ar_instance_ID', $this->get_default('__ar_instance_ID', $this->new_instance_ID()));
        if (isset(Ar::$instances[$this->__ar_instance_ID])) {
            return '__RECURSION__';
        }
        if (true === $full) {
            //@todo removetimelimit stuff;
            set_time_limit(2);
            return $this->full_export();
            set_time_limit(30);
        }
        else {
            return $this->state;
        }
    }

    public function get($key) {
        if (!array_key_exists($key, $this->state)) {
            throw new InvalidKeyException($key, $this);
        }
        return $this->state[$key];
    }

    public function get_default($key, $default_value) {
        try {
            $value = $this->get($key);
            return $value;
        } catch (InvalidKeyException $e) {
            return $default_value;
        }
    }

    public function get_callback($key, $fun, $args=array()) {
        try {
            $value = $this->get($key);
            return $value;
        } catch (InvalidKeyException $e) {
            return call_user_func_array($fun, $args);
        }
    }

    // essaie toutes les clés passées en paramètre jusqu'à ce qu'on en
    // trove une et retourne sa valeur
    // si aucune n'est trouvée, déclenche une InvalidKeyException avec
    // la dernière essayée
    public function any($_keys) {
         if(is_array($_keys)) {
            $keys = $_keys;
        }
        else {
            $keys = func_get_args();
        }
        $key = array_shift($keys);
        try {
            return $this->get($key);
        } catch (InvalidKeyException $e) {
            if (count($keys)) {
                return $this->any($keys);
            }
            else  throw $e;
        }
    }

    public function extract($_keys) {
        if(is_array($_keys)) {
            $keys = $_keys;
        }
        else {
            $keys = func_get_args();
        }
        //@todo trouver une implémentation native
        $extract = array();
        foreach($keys as $key) {
            $extract[$key] = $this->get_default($key, null);
        }
        return $extract;
    }

    // renvoie un clone avec uniquement les clés indiquées
    public function sel_copy($_keys) {
        if(is_array($_keys)) {
            $keys = $_keys;
        }
        else {
            $keys = func_get_args();
        }
        $data = $this->extract($keys);
        return new \micromachine\Ar($data);
    }

    public function set($key, $value) {
        $this->state[$key] = $value;
        return $this;
    }

    public function set_new($key, $value) {
        try {
            $this->get($key);
            throw new ExistingKeyException($key, $this);
        } catch (InvalidKeyException $e) {
            $this->set($key, $value);
        }
        return true;
    }

    // ajoute à la liste $key un élément. si la clé n'existe pas,
    // elle est créée avec un array vide.

    public function push_to($key, $elem) {
        $stack = $this->get_default($key, array());
        array_push($stack, $elem);
        $this->set($key,$stack);
    }

    // alias de push_to
    public function append_to($key, $elem) {
        $this->push_to($key, $elem);
    }

    // dépile un élément de la liste $key, si la liste n'existe pas,
    // retourne $default ($default vaut NULL par défaut)
    public function pop_from($key, $default=null) {
        $stack = $this->get_default($key, array());
        if(0 == count($stack)) {
            return $default;
        }
        $elem = array_pop($stack);
        $this->set($key,$stack);
        return $elem;
    }

    public function __get($key) {
        if($key === 'qzd') r('hoho');
        return $this->get($key);
    }

    public function __set($_, $_) {
        throw new \BadMethodCallException('Direct key set is not allowed in ' . get_class($this));
    }

    public function replace($key, $value) {
        $this->get($key); // ensure existing value
        return $this->set($key, $value);
    }

    /*
     * Réalise un import mais sans écraser les clés déjà existantes
     */

    public function merge_new(array $values) {
        $new_state = array_merge($values, $this->export());
        $this->state = $new_state;
        return $this;
    }

    public function keys() {
        return array_keys($this->state);
    }

    public function has($key) {
        return array_key_exists($key, $this->state);
    }

    public function remove($key) {
        if($this->has($key)) {
            unset($this->state[$key]);
        }
        return true;
    }

    public function map(/* callable */ $fun) {
        $mapped = array();
        foreach ($this->state as $k => $v) {
            $mapped[$k] = $fun($v, $k);
        }
        return $mapped;
    }



    public function classify(/* callable */ $fun) {
        // cette fonction permet de renvoyer les items du state
        // classés selon le résultat de la fun sur chacun d'eux.
        // les groupes d'items retournés ont pour clé ces résultats
        // les clés originales sont préservées
        $classified = array();
        foreach($this->state as $key => $item) {
            $v = $fun($item);
            // // utile que si on utilise l'opérateur [] :
            // if(!isset($classified[$v])) {
            //     $classified[$v] = array();
            // }
            $classified[$v][$key] = $item;
        }
        return $classified;
    }

    private function full_export() {
        // comme exporte, sauf dans le cas ou l'élément de this->state
        // est aussi une instance de Ar alors on appelle récursivement
        // full_export
        // dans le cas ou deux Ar se contiennent mutuellement, pour
        // éviter la boucle_infine, on utilise une variable pour voir
        // si on est déjà passés
        $export = array();
        //*/
        $STOP =  new \Exception("

            Apparemment on a une boucle infinie …

            Cette méthode ne doit pas être utilisée, car dans le cas
            d'un import il n'y a pas encore la possibilité de re-set
            les objets récursifs.
        ");
        // throw $STOP;

        //*/
        foreach($this->state as $k => $v) {
            if(is_a($v, '\micromachine\Ar')) {
                $export[$k] = $v->export(true);
            }
            else {
                $export[$k] = $v;
            }
        }
        return $export;
    }

    public function to_json() {
        return json_encode($this->state);
        //@todo first export recursive
    }

}

class Ar_Default_Getter {
    // Cette classe permet d'obtenir un objet que l'on pourra
    // interroger comme un Ar mais qui renverra null sur une clé
    // existante au lieu de renvoyer une exception
    protected  $target;

    public function __construct(\micromachine\Ar $target) {
        $this->target = $target;
    }

    public function get($key) {
        return $this->target->get_default($key,null);
    }

    public function __get($key) {
       return $this->get($key);
    }
}

class InvalidKeyException extends \Exception {

    public function __construct($key, $Ar, $message=null) {
        $error_msg = '['.get_class($Ar).'] ' . (is_null($message) ? "Invalid key [$key]" : $message);

        parent::__construct($error_msg);
    }

}

class ExistingKeyException extends \Exception {

    public function __construct($key, $Ar, $message=null) {
        $error_msg = '['.get_class($Ar).'] ' . (is_null($message) ? "Key [$key] already exists" : $message);

        parent::__construct($error_msg);
    }

}
