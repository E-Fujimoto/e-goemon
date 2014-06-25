<?php
class System_View_Helper_Token extends Zend_View_Helper_Abstract
{
    private $_token = null;

    public function __construct()
    {
        if ($this->_token === null) {
            $this->_token = Zend_Controller_Action_HelperBroker::getStaticHelper('token')->get();
        }
    }

    public function token()
    {
        return $this->_token;
    }

}
