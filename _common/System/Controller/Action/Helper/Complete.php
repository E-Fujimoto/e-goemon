<?php
class System_Controller_Action_Helper_Complete extends Zend_Controller_Action_Helper_Abstract
{
    private function _getSession()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('session')->start();
    }

    public function set()
    {
        $session = $this->_getSession();

        if (!is_null($session)) {
            $session->complete = 1;
        }
    }

    public function get()
    {
        $session = $this->_getSession();

        $complete = null;
        if (isset($session->complete)) {
            $complete = $session->complete;

            unset($session->complete);
        }

        return $complete;
    }

}
