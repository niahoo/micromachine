<?php

// Pour faire fonctionner l'extension il faut télécahrger
// https://github.com/fabpot/Twig-extensions
// et mettre le dossier Extensions dans le dossier Twig/twig
// du dossier vendor

class mod_twigi18n {


    public static function init(\micromachine\Context $context) {

        $context->twig->addExtension(new Twig_Extensions_Extension_I18n());
    }
}
