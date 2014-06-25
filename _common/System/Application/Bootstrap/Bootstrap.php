<?php
class System_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoloader()
    {
        $config = array();

        if (file_exists(APPLICATION_PATH . '/modules')) {
            $dir = dir(APPLICATION_PATH . '/modules');
            while ($basePath = $dir->read()) {
                if ($basePath != '.' && $basePath != '..') {
                    $config['basePath']  = APPLICATION_PATH . '/modules/' . $basePath;
                    $config['namespace'] = ucfirst(strtolower($basePath));

                    new Zend_Application_Module_Autoloader($config);
                }
            }
            $dir->close();
        } else {
            $config['basePath']  = APPLICATION_PATH;
            $config['namespace'] = '';

            new Zend_Application_Module_Autoloader($config);
        }
    }

    public function setRegistry($config)
    {
        $registry = Zend_Registry::getInstance();

        foreach ($config as $name => $file) {
            $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            switch ($suffix) {
                case 'ini':
                    $data = new Zend_Config_Ini($file, null, array('allowModifications' => true));
                    break;

                case 'xml':
                    $data = new Zend_Config_Xml($file);
                    break;

                case 'php':
                case 'inc':
                    $data = include $file;
                    if (!is_array($data)) {
                        throw new Zend_Application_Exception('Invalid configuration file provided; PHP file does not return array value');
                    }
                    return $data;
                    break;

                default:
                    throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
            }

            $registry->set($name, $data);
        }

        return $this;
    }

}
