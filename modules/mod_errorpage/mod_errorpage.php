<?php

class mod_errorpage {

    public static function init($context) {
        $context->set('errorpages', new self($context));
    }

    public static function get_error_description($code) {
        $description = '';
        switch ($code) {
            case '403': $description = _('Sorry, but this page is private.'); break;
            case '404': $description = _('Sorry, but the page you are looking for doesn\'t seem to exist !'); break;
            case '500': $description = _('Server error'); break;
            case '501': $description = _('This functionality is not available at the moment.'); break;
        }
        return sprintf('<p>%s</p>', $description);
    }

    private $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function render($code,$tpl_vars=array()) {

        $base_vars['code'] = $code;
        $base_vars['error_msg'] = self::get_error_description($code);

        $tpl_vars = array_merge($base_vars,$tpl_vars);

        try {
            $body = $this->context->render("$code.html",$tpl_vars);
        }
        catch ( Twig_Error_Loader $e) {
            $body = $this->context->render('error.html',$tpl_vars);
        }

        return Response($body, $headers=array(), intval($code));
    }

}
