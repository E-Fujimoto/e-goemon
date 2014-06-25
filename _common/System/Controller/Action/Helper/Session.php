<?php
class System_Controller_Action_Helper_Session extends Zend_Controller_Action_Helper_Abstract
{
    private $_session = array();

    public function getNamespace($namespace = null)
    {
        if (is_null($namespace) || empty($namespace)) {
            $module     = $this->getRequest()->getModuleName();
            $controller = $this->getRequest()->getControllerName();

            $config = Zend_Registry::getInstance()->get('config');

            if (isset($config->namespace->{$module}->{$controller})) {
                $namespace = $config->namespace->{$module}->{$controller};
            } else {
                $namespace = 'NamespaceUnknown';
            }
        }

        return $namespace;
    }

    public function start($namespace = null)
    {
        $namespace = $this->getNamespace($namespace);

        if (!Zend_Session::isStarted()) {
            Zend_Session::setOptions(array('use_trans_sid' => 1, 'use_only_cookies' => 0));
        }

        if (!is_null($namespace)) {
            if (!isset($this->_session[$namespace]) || !($this->_session[$namespace] instanceof Zend_Session_Namespace)) {
                $this->_session[$namespace] = new Zend_Session_Namespace($namespace);
            }
        }

        return isset($this->_session[$namespace]) ? $this->_session[$namespace] : null;
    }

    public function end($namespace = null)
    {
        $namespace = $this->getNamespace($namespace);

        if (isset($this->_session[$namespace])) {
            unset($this->_session[$namespace]);
        }
        if (Zend_Session::namespaceIsset($namespace)) {
            Zend_Session::namespaceUnset($namespace);
        }
    }

    public function destroy()
    {
        $this->_session = array();

        if (Zend_Session::isStarted()) {
            Zend_Session::destroy();
        }
    }

}
