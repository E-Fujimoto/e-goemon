<?php
class System_Controller_Action_Helper_GetDate extends Zend_Controller_Action_Helper_Abstract
{
    public function get($key = null)
    {
        $data = '';

        if (preg_match('/(\d{4})[\D](\d{2})[\D](\d{2})/', $key, $match) || preg_match('/(\d{4})(\d{2})(\d{2})/', $key, $match)) {
            $data = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_date')->reset()
                                                                                                        ->set('Y', $match[1])
                                                                                                        ->set('m', $match[2])
                                                                                                        ->set('d', $match[3])
                                                                                                        ->getDate('/');
        }

        return $data;
    }

    public function direct($key = null)
    {
        return $this->get($key);
    }

}
