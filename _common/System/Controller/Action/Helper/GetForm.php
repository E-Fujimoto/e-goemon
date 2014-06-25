<?php
class System_Controller_Action_Helper_GetForm extends Zend_Controller_Action_Helper_Abstract
{
    public function get($keys = null)
    {
        $data = array();

        if ($keys === null) {
            $data = $this->getRequest()->getPost();
        } else if (is_array($keys)) {
            foreach ($keys as $key) {
                $data[$key] = Zend_Controller_Action_HelperBroker::getStaticHelper('request')->getPost($key);
            }
        } else {
            $data[$keys] = Zend_Controller_Action_HelperBroker::getStaticHelper('request')->getPost($keys);
        }

        return $data;
    }

    public function direct($keys = null)
    {
        return $this->get($keys);
    }

}
