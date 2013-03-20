<?php

class Redbean_Tree {

    const left = 'rbtree_left';
    const right = 'rbtree_right';
    const level = 'rbtree_level';

    public function __construct($bean_type) {
        $this->bt = $bean_type;
    }

    public function findOne($sql=null, $values = array()) {
        $found = R::findOne($this->bt, $sql, $values);
        if(!is_null($found)) {            
            $found->setMeta('tree', $this);
        }
        return $found;
    }

    public function find($sql=null, $values = array()) {
        $found = R::find($this->bt, $sql, $values);
        foreach($found as $f) {
            $f->setMeta('tree', $this);
        }
        return $found;
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
        return $found;
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
            R::store($child);
            R::commit();
        } catch(Exception $e) {
            R::rollback();
            throw $e;
        }


    }
}