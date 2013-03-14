<?php

class mod_twig {


    public static function init(\micromachine\Context $context) {
        $c = $context->conf;
        Twig_Autoloader::register();
        $cache_enabled = $c->get_default('twig.cache', false);
        $twig_opts = array();

        $tpl_dirs = $c->templates_dirs;

        if($cache_enabled) {
            $default_cache_dir = $c->app_root . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'twig';
            $twig_cache_dir = $c->get_default('twig.cache_dir', $default_cache_dir);
            $twig_auto_reload = $c->get_default('twig.auto_reload', false);
            $twig_opts['cache'] = $twig_cache_dir;
            $twig_opts['auto_reload'] = $twig_cache_dir;
        }

        $twig_opts['debug'] = $c->get_default('twig.debug', false);



        $loader = new Twig_Loader_Filesystem($tpl_dirs);

        $twig = new Twig_Environment($loader, $twig_opts);

        // on ajoute le context à twig pour pouvoir y accéder depuis
        // les templates

        $twig->addGlobal('c', new mod_twig_ar_caller($context));

		// On ajoute quelques fonctions à Twig

        if($twig_opts['debug']) {
            $twig->addExtension(new Twig_Extension_Debug());
        }

        // - notre extension avec le parseur d'url, helpers pour forms, session flash
        require_once __DIR__ . '/lib/extension.php';
        $twig->addExtension(new Mod_Twig_Extension($context));

        // - Markdown
        $context->require_lib('Markdown', '-v1.0.1o', array('markdown.php'));
        $twig->addFilter('mk', new Twig_Filter_Function('Markdown'));
        $twig->addFilter('markdown', new Twig_Filter_Function('Markdown'));

        // on ajoute au context l'instance de Twig_Environment sous le
        // nom de 'twig' pour y accéder directement

        $context->set('twig',$twig);

        // on crée aussi la méthode 'render' dans le contexte pour
        // appeller directement render depuis le contexte

        $context->set('render',function($template_name, $tpl_vars=array()) use ($context) {
            return mod_twig::render($template_name, $tpl_vars, $context);
        });
    }



    static function render($template_name, $tpl_vars, \micromachine\Context $context) {
        $twig = $context->twig;
        $template = $twig->loadTemplate($template_name);
        return $template->render($tpl_vars);
    }
}

// Cette classe permet à Twig de récupérer des clefs dans un Ar en
// gardant la dot-syntax de Twig Cela permet de récupérer les clefs
// d'un Ar dans un template, mais pas d'appeler les fonctions de la
// classe qui étend Ar. Pour cela, il faut d'abord appeler .f dans le
// template
class  mod_twig_ar_caller {
    private $ar;
    private $target;

    public function __construct(\micromachine\Ar $ar) {
        $this->ar = $ar;
        $this->target = new \micromachine\Ar_Default_Getter($ar);
    }

    public function __call($key, $_)  {
        $value = $this->target->get($key);
        if($value instanceof \micromachine\Ar) {
            return new mod_twig_ar_caller($value);
        }
        return $value;
    }
    public function f() {
        return new mod_twig_ar_function_call($this->ar);
    }
}

// cette classe prend un Ar et permet d'appeler les fonction définies
// dans la classe qui étend Ar
class mod_twig_ar_function_call {
    private $ar;

    public function __construct(\micromachine\Ar $ar) {
        $this->ar = $ar;
    }

    public function __call($key,$args) {
        return call_user_func_array(array($this->ar, $key), $args);
    }
}
