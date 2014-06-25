<?php
class System_Controller_Action_Helper_Token extends Zend_Controller_Action_Helper_Abstract
{
    private $_token = null;

    private function _getNamespace()
    {
        if (isset(Zend_Registry::getInstance()->get('config')->namespace->token->session)) {
            $namespace = Zend_Registry::getInstance()->get('config')->namespace->token->session;
        } else {
            $namespace = 'Token';
        }

        return $namespace;
    }

    private function _startSession()
    {
        // セッションヘルパー取得
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');

        // トークンセッション用名前空間名取得
        $namespace = $this->_getNamespace();

        // トークンセッション開始
        $session = $helper->start($namespace);

        return $session;
    }

    public function set()
    {
        // トークンセッション取得
        $session = $this->_startSession();

        // トークン取得
        $token = sha1(uniqid(mt_rand(), true));

        // セッションにトークンを格納
        $session->data = $token;

        // View にトークンを格納
        $this->_actionController->view->token = $token;
    }

    public function get()
    {
        if ($this->_token === null) {
            // トークンセッション取得
            $session = $this->_startSession();

            // トークン取得
            $this->_token = sha1(uniqid(mt_rand(), true));

            // セッションにトークンを格納
            $session->data = $this->_token;
        }

        return $this->_token;
    }

    public function isValid()
    {
        // トークンセッション開始
        $session = $this->_startSession();

        // セッション中のトークン取得
        if (isset($session->data)) {
            $sessionToken = $session->data;

            unset($session->data);
        } else {
            $sessionToken = '';
        }

        // リクエスト中のトークン取得
        if ($this->getRequest()->isPost()) {
            $requestToken = $this->getRequest()->getPost('token', '');
        } else {
            $requestToken = $this->getRequest()->getParam('token', '');
        }

        // トークン比較
        if ($sessionToken === $requestToken) {
            return true;
        } else {
            // エラーメッセージの取得
            if (isset(Zend_Registry::getInstance()->get('config')->error->token)) {
                $error = Zend_Registry::getInstance()->get('config')->error->token;
            } else {
                $error = 'セキュリティエラーが発生しました';
            }

            // エラーヘルパーにエラーメッセージを格納
            Zend_Controller_Action_HelperBroker::getStaticHelper('error')->set('token', $error);
        }

        return false;
    }

}
