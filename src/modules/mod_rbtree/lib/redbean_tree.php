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

    public function append_child($parent, $child) {

        if($child->id !== 0) {
            throw new Exception('le noeud existe déjà, il faut donc déplacer un sous arbre');
        }

        $table = $this->bt;
        $prb = $parent->right_bound();
        $left = self::left;
        $right = self::right;

        R::begin();
        try {
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
