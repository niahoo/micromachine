<?php



class TreeTest {

	public function index ($context) {
        $context->require_part('model','Tree_Node');
        $context->require_part('model','Model_Topic');
		$tree = mod_rbtree::setup_tree('topic');
        $root = $tree->root();

        $parent = $tree->findOne('1 ORDER BY RANDOM() LIMIT 1') ;


        $sub = R::dispense('topic');
        $sub->name = 'hohoho';

        // $parent->append_child($sub);

        r("\n".$this->getChildren($root));


	}

    function getChildren($node, $tab=0) {
        

        $children = array();        

        $stack = str_repeat("\t", $tab) . "$node->id --- $node->name" . "\n";
        
        // rx(array_keys($node->children()));

        foreach($node->children() as $child) {
            if($tab == 0) r($child->id);
            $stack .= $this->getChildren($child, $tab+1);
        }

        return $stack;
    }

}