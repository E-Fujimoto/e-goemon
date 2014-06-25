<?php
class System_Controller_Action_Helper_Redirect extends Zend_Controller_Action_Helper_Abstract
{
    public function go($url, $options = array())
    {
        // 携帯であれば URL にセッションと guid を付加する
        if (Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_mobile_agent')->isMobile()) {
            if (Zend_Session::isStarted()) {
                $session = session_name() . '=' . session_id();

                if (strpos($url, '?') !== false) {
                    list($url, $query) = explode('?', $url, 2);

                    $tmp   = explode('&', $query);
                    $tmp[] = $session;
                    $tmp[] = 'guid=on';
                    $tmp   = array_values(array_unique($tmp));

                    $url .= '?' . join('&', $tmp);
                } else {
                    $url  = rtrim($url, '/');
                    $url .= '/?' . $session . '&guid=on';
                }
            }
        }

        // エラーメッセージをセッションに格納
        Zend_Controller_Action_HelperBroker::getStaticHelper('error')->setSession();

        // リダイレクト実行
        Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl($url, $options);
    }

    public function direct($url, $options = array())
    {
        return $this->go($url, $options);
    }

}
