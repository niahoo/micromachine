<?php

class mod_redbean {

    const default_version = '-v3.3.4';

    public static function init(\micromachine\Context $context) {

        // 1. Loading

        $redbean_version = $context->conf->get_default('rb.version', self::default_version);
        $context->require_lib('redbeanphp', $redbean_version, array('rb.php'));

        // 2. Config

        // simple global config as with facade.
        $dsn = $context->conf->get_default('rb.dsn', null);
        $username = $context->conf->get_default('rb.username', null);
        $password = $context->conf->get_default('rb.password', null);
        $freeze = $context->conf->get_default('rb.freeze', false);
        if ($freeze) {
            R::freeze();
        }
        R::setup($dsn,$username,$password);

        // 3. Context injection

        $di = new RedBean_DependencyInjector;
        RedBean_ModelHelper::setDependencyInjector( $di );

        $di->addDependency('Context', $context);

        // 4. Logging

        if ($context->conf->get_default('rb.log', false)) {
            $adapter = R::$adapter;
            $logger = RedBean_Plugin_QueryLogger::getInstanceAndAttach($adapter);
            register_shutdown_function(function () use ($logger, $context) {
                mod_redbean::flush_logs($logger, $context);
            });
        }
    }

    public static function flush_logs($logger, $context) {
        $logdir = $context->conf->get('rb.log.dir');
        if (!is_dir($logdir)) {
            mkdir($logdir, 0770, true);
        }
        // la rotation des fichiers se fait simplement en fonction du
        // temps qui est indiqué dans le nom du fichier. la précision
        // se fait par heures par défaut, mais la config permet de
        // surcharger ce format
        $timefmt = $context->conf->get_default('rb.log.filetimeformat','Y-m-d-H');
        $logbase = 'rb-log-' . v(new DateTime())->format($timefmt);
        $logfile = "$logdir/$logbase.sql";
        $buffer = '';
        foreach ($logger->getLogs() as $logline) {
            $buffer .=  "\n" . $logline . "\n\n";
        }

        //@todo utiliser flock pour locker le fichier
        if (strlen($buffer)) {
            $f = fopen($logfile, 'a');
            fwrite($f, mod_redbean::log_header().$buffer);
            fclose($f);
        }
    }

    public static function log_header() {
        return str_pad(
            "\n".'-- QUERY LOG -- ' . date('d-M-Y::H:i:s') . ' ---',
        70, '-');
    }
}

