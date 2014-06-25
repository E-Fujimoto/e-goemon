<?php
class Bootstrap extends System_Application_Bootstrap_Bootstrap
{
    protected function _initRoute()
    {
        $this->bootstrap('frontController');
        $router = $this->getResource('FrontController')->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/router.ini');
        $router->addConfig($config, 'routes');
    }

}
