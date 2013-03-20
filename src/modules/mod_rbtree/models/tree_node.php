<?php

class Tree_Node extends RedBean_SimpleModel {

    public function left_bound() {
        return $this->bean[Redbean_Tree::left];
    }

    public function right_bound() {
        return $this->bean[Redbean_Tree::right];
    }

    public function level() {
        return $this->bean[Redbean_Tree::level];
    }

    public function tree() {
        return $this->bean->getMeta('tree');
    }

    public function children() {
        return $this->tree()->children($this);
    }

    public function append_child($bean) {
        return $this->tree()->append_child($this, $bean);
    }
}