<?php
class System_Controller_Action_Helper_Request extends Zend_Controller_Action_Helper_Abstract
{
    public function getQuery($key = null, $default = null)
    {
        $agent = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_mobile_agent');

        if ($agent->isMobile()) {
            if (is_string($key) || is_numeric($key)) {
                require_once 'HTML/Emoji.php';
                $emoji = HTML_Emoji::getInstance($agent->getName());
                $emoji->setConversionRule('kokogiko');

                return $emoji->filter($this->getRequest()->getQuery($key, $default), 'Input');
            }
        } else {
            return $this->getRequest()->getQuery($key, $default);
        }

        return null;
    }

    public function getPost($key = null, $default = null)
    {
        $agent = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_mobile_agent');

        if ($agent->isMobile()) {
            if (is_string($key) || is_numeric($key)) {
                require_once 'HTML/Emoji.php';
                $emoji = HTML_Emoji::getInstance($agent->getName());
                $emoji->setConversionRule('kokogiko');

                return $emoji->filter($this->getRequest()->getPost($key, $default), 'Input');
            }
        } else {
            return $this->getRequest()->getPost($key, $default);
        }

        return null;
    }

    public function getParam($key = null, $default = null)
    {
        $agent = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_mobile_agent');

        if ($agent->isMobile()) {
            if (is_string($key) || is_numeric($key)) {
                require_once 'HTML/Emoji.php';
                $emoji = HTML_Emoji::getInstance($agent->getName());
                $emoji->setConversionRule('kokogiko');

                return $emoji->filter($this->getRequest()->getParam($key, $default), 'Input');
            }
        } else {
            return $this->getRequest()->getParam($key, $default);
        }

        return null;
    }

}
