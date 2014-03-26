<?php

namespace micromachine;

Class CodeGen {

    static function wrap ($str) {
        return "<?php\n$str\n?>\n";
    }

    static function soft_wrap ($str) {
        return "<?php\n$str\n";
    }

    static function build_exception($name) {
        return self::soft_wrap("class $name extends RuntimeException {}");
    }
}
