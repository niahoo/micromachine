<?php

// Ici nous faisons toutes les insersions par la droite, afin d'avoir
// la racine positionnée sur 1 en borne gauche

class mod_rbtree {
    
    static public function setup_tree($bean_type) {
        require_once __DIR__ . '/lib/redbean_tree.php';
        $tree = new Redbean_Tree ('topic');
        $frozen = R::$toolbox->getRedBean()->isFrozen();
        if(!$frozen) {
            // création du noeud racine s'il n'existe pas
            $root = $tree->root();
            
            if(is_null($root)) {
                $tree->create_root();
            }
        }
        return $tree;
    }

}