<?php
/**
 * 認証情報保存クラス
 *
 * セッションを利用してユーザーの認証を保持する
 *
 * @category  System
 * @package   System
 * @copyright Copyright 2014 SDM-Summit
 * @license   New BSD License
 * @version   $Id$
 */
class System_Auth
{
    private $_auth = null;

    public function __construct()
    {
        $namespace   = Zend_Registry::getInstance()->get('config')->namespace->auth->storage;
        $this->_auth = Zend_Auth::getInstance();

        $this->_auth->setStorage(new Zend_Auth_Storage_Session($namespace));
    }

    public function hasId()
    {
        return $this->_auth->hasIdentity();
    }

    public function setId($id)
    {
        $this->_auth->getStorage()->write($id);

        return $this;
    }

    public function getId()
    {
        return $this->_auth->getIdentity();
    }

    public function clearId()
    {
        $this->_auth->clearIdentity();

        return $this;
    }

}
