<?php

class Redbean_Tree {

    const left = 'rbtree_left';
    const right = 'rbtree_right';
    const level = 'rbtree_level';

    public function __construct($bean_type) {
        $this->bt = $bean_type;
    }

    public function load($id) {
        $found = $found = R::load($this->bt, $id);
        return $this->add_meta($found);
    }

    public function findOne($sql=null, $values = array()) {
        $found = R::findOne($this->bt, $sql, $values);
        return $this->add_meta($found);
    }

    public function find($sql=null, $values = array()) {
        $found = R::find($this->bt, $sql, $values);
        return $this->add_meta($found);
    }

    public function dispense() {
        $bean = R::dispense($this->bt);
        return $this->add_meta($bean);
    }

    public function root() {
        return $this->findOne(self::left . ' = ? LIMIT 1', array(1));
    }

    public function create_root() {
        $bean = R::dispense($this->bt);
        $bean->import(array(
            self::left => 1
          , self::right => 2
          , self::level => 0
          //@todo gérer le niveau
        ));
        $bean->setMeta('tree' , $this);
        $bean->setMeta('buildcommand.unique', array(array(self::left, self::right)));
        R::store($bean);
    }

    public function children($bean) {
        $found = $this->find(
            self::left  . ' > ? AND ' .
            self::right . ' < ? AND ' .
            self::level . ' = ?'
            ,
            array(
                $bean->left_bound() ,
                $bean->right_bound() ,
                $bean->level() + 1
            )
        );
        return $this->add_meta($found);
    }

    private function move_node($parent, $child) {
        r($child);
    }

    public function append_child($parent, $child) {

        if($child->id !== 0) {
            return $this->move_node($parent, $child);
        }

        $table = $this->bt;
        $left = self::left;
        $right = self::right;

        try {
            R::begin();
            $prb = R::getCell("SELECT $right FROM $table
                                WHERE id = ? ", array($parent->id));
            R::exec("UPDATE $table SET $right = $right + 2
                     WHERE $right >= $prb");
            R::exec("UPDATE $table SET $left = $left + 2
                     WHERE $left >= $prb");
            $child[self::left] = $prb;
            $child[self::right] = $prb + 1;
            $child[self::level] = $parent->level() + 1;
            $this->add_meta($child);
            R::store($child);
            R::commit();
        } catch(Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    public function trash ($node) {
        // supprime un sous-arbre complet à partir du noeud $node
        // inclus
        if($node->id === 0) {
            throw new InvalidArgumentException('Non existent node');
        }
        else {
            $left = self::left;
            $right = self::right;
            $node_left = $node[self::left];
            $node_right = $node[self::right];
            $table = $this->bt;
            // Après la suppression, on va décaler vers la gauche tous
            // les éléments. On les décale tous de la largeur du node
            // supprimé + 1
            // (on ajoute 1 pour récupérer la distance entre le node
            // supprimé et son voisin de droite qui vient le
            // remplacer)
            $left_move = $node_right - $node_left + 1;
            try {
                R::begin();
                $prb = R::getCell("DELETE FROM $table
                                    WHERE $left >= ? AND $right <= ?", array($node_left, $node_right));
                R::exec("UPDATE $table SET $right = $right - $left_move
                         WHERE $right >= $node_left");
                R::exec("UPDATE $table SET $left = $left - $left_move
                         WHERE $left > $node_left");
                R::commit();
            } catch(Exception $e) {
                R::rollback();
                throw $e;
            }

        }
    }

    private function add_meta($beans) {
        if(is_array($beans)) {
            foreach($beans as $bean) {
                $bean->setMeta('tree', $this);
            }
        }
        elseif(is_object($beans)) {
             $beans->setMeta('tree', $this);
        }
        elseif (null === $beans) {
            // null is ok, it's a not found bean
        }
        else {
            throw new InvalidArgumentException('Wrong data');
        }
        return $beans;
    }
}
