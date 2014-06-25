<?php
class System_Controller_Action_Helper_Error extends Zend_Controller_Action_Helper_Abstract
{
    private $_errors = array();

    public function __set($name, $value)
    {
        $this->_errors[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->_errors[$name]) ? $this->_errors[$name] : null;
    }

    public function set($name, $value = null)
    {
        if (is_array($name)) {
            $this->_errors = array_merge_recursive($this->_errors, $name);
        } else if (!empty($name)) {
            $this->__set($name, $value);
        }

        return $this;
    }

    private function _getNamespace()
    {
        if (isset(Zend_Registry::getInstance()->get('config')->namespace->error->session)) {
            $namespace = Zend_Registry::getInstance()->get('config')->namespace->error->session;
        } else {
            $namespace = 'Error';
        }

        return $namespace;
    }

    private function _startSession()
    {
        // セッションヘルパー取得
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');

        // エラーセッション用名前空間名取得
        $namespace = $this->_getNamespace();

        // エラーセッション開始
        $session = $helper->start($namespace);

        return $session;
    }

    private function _endSession()
    {
        // セッションヘルパー取得
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');

        // 名前空間名取得
        $namespace = $this->_getNamespace();

        // セッション停止
        $helper->end($namespace);
    }

    public function getAll()
    {
        // セッション取得
        $session = $this->_startSession();

        // エラーセッション中のデータを取得
        if (isset($session->data) && is_array($session->data)) {
            $this->_errors = array_merge($this->_errors, $session->data);
        }

        // エラーセッションの削除
        $this->_endSession();

        return $this->_errors;
    }

    public function setSession()
    {
        // セッション取得
        $session = $this->_startSession();

        // エラーセッション中にデータを格納
        $session->data = $this->_errors;
    }

}
