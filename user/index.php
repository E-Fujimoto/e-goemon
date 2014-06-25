<?php
defined('APPLICATION_ROOT') || define('APPLICATION_ROOT', realpath(dirname(dirname(__FILE__))));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', APPLICATION_ROOT . '/_user');
defined('APPLICATION_VIEW') || define('APPLICATION_VIEW', APPLICATION_ROOT . '/user');
defined('APPLICATION_ENV')  || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? get('APPLICATION_ENV') : 'development'));
// defined('APPLICATION_ENV')  || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? get('APPLICATION_ENV') : 'production'));
defined('LIBRARY_PATH')     || define('LIBRARY_PATH', APPLICATION_ROOT . '/_common');

require_once 'Zend/Application.php';

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()
            ->run();
