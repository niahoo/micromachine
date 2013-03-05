<?php

namespace micromachine;

define('MMROOT',dirname(__FILE__));

define('DEFAULT_CONTROLLER_ACTION_NAME', 'index');

require_once MMROOT . '/src/utils.php';
require_once MMROOT . '/src/codegen.php';
require_once MMROOT . '/src/ar.php';
require_once MMROOT . '/src/config.php';
require_once MMROOT . '/src/context.php';
require_once MMROOT . '/src/module.php';
require_once MMROOT . '/src/controllerhandler.php';
require_once MMROOT . '/src/response.php';
require_once MMROOT . '/src/sessionhandler.php';

require_once MMROOT . '/lib/PHP-Router/Route.php';
require_once MMROOT . '/lib/PHP-Router/Router.php';

require_once MMROOT . '/lib/phptools/webrequest/webrequest.class.php';

require_once MMROOT . '/lib/Cookie.php';

require_once MMROOT . '/src/micromachine.php';

