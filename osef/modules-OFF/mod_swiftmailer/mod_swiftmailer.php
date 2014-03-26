<?php

class mod_swiftmailer {

    const default_version = '-4.2.1';

    public static function init(\micromachine\Context $context) {

        // 1. Loading

        $version = $context->conf->get_default('swift.version', self::default_version);
        $context->require_lib('Swift', $version, array('lib/swift_required.php'));

        // 2. Config

        $config = $context->conf->get_default('swift.config_fun',function () {
            Swift_DependencyContainer::getInstance()
                ->register('mime.qpcontentencoder')
                ->asAliasOf('mime.nativeqpcontentencoder');
        });

        Swift::init($config);


        // on utilise une closure pour ne pas charger toutes les
        // bibliothèques au démarrage de l'appli alors qu'il n'y a
        // que peu de controlleurs qui envoient des emails généralement

        $get_mailer = function() use($context) {
            static $mailer = null;
            if (! ($mailer instanceof Swift_Mailer)) {
                $transport_type = $context->conf->get_default('swift.transport', 'smtp');

                switch($transport_type) {
                    case 'smtp':
                        $port = $context->conf->get_default('smtp.port', 25);
                        $hostname = $context->conf->get('smtp.host');
                        $username = $context->conf->get('smtp.user');
                        $password = $context->conf->get('smtp.password');
                        $encrypt = $context->conf->get_default('smtp.encryption', null);
                        $transport = Swift_SmtpTransport::newInstance($hostname,$port,$encrypt)
                            ->setUsername($username)
                            ->setPassword($password)
                        ;
                        break;
                    default: rx('Not implemented');
                }
                $mailer = Swift_Mailer::newInstance($transport);
            }
            return $mailer;
        };


        $context->set('get_mailer',$get_mailer);

    }
}

