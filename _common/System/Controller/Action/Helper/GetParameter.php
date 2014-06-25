<?php
class System_Controller_Action_Helper_GetParameter extends Zend_Controller_Action_Helper_Abstract
{
    public function get($session, $key, $default = null)
    {
        $data = $default;

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->has($key)) {
                $data = Zend_Controller_Action_HelperBroker::getStaticHelper('request')->getPost($key, $default);
            }
        } else {
            if ($this->getRequest()->has($key)) {
                $data = Zend_Controller_Action_HelperBroker::getStaticHelper('request')->getParam($key, $default);
            } else if (isset($session->{$key})) {
                $data = $session->{$key};
            }
        }

        return $data;
    }

    public function direct($session, $key, $default = null)
    {
        return $this->get($session, $key, $default);
    }

}
