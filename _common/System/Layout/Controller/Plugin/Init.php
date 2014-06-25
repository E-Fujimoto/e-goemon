<?php
class System_Layout_Controller_Plugin_Init extends Zend_Layout_Controller_Plugin_Layout
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = $this->_layout->getView();

        parent::preDispatch($request);

        $view->moduleName     = $request->getModuleName();
        $view->controllerName = $request->getControllerName();
        $view->actionName     = $request->getActionName();
        $view->refer          = $request->getServer('HTTP_REFERER');

        // リファー設定
        if (empty($view->refer)) {
            if ($view->moduleName === Zend_Controller_Front::getInstance()->getDefaultModule()) {
                if ($view->controllerName === Zend_Controller_Front::getInstance()->getDefaultControllerName()) {
                    $view->refer = '/';
                } else {
                    $view->refer = '/' . $view->controllerName . '/';
                }
            } else {
                $view->refer = '/' . $view->moduleName . '/' . $view->controllerName . '/';
            }
        }
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = $this->_layout->getView();

        // デバッグ時に DB プロファイルを取得する
        if ($view->debug) {
            $db = Zend_Db_Table::getDefaultAdapter();

            if (!empty($db) && $db instanceof Zend_Db_Adapter_Abstract) {
                $profiler = $db->getProfiler();

                $view->queryCount = $profiler->getTotalNumQueries();
                $view->totalTime  = $profiler->getTotalElapsedSecs();
                $view->profiles   = $profiler->getQueryProfiles();
            }
        }

        parent::postDispatch($request);

        // 携帯用
        $agent = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_mobile_agent');
        if ($agent->isMobile()) {
            require_once 'HTML/Emoji.php';
            $emoji = $emoji = HTML_Emoji::getInstance($agent->getName());
            $emoji->setConversionRule('kokogiko');

            if ($agent->isDocomo()) {
                $charset = 'Shift_JIS';

                $this->getResponse()->setHeader('Content-Type', 'application/xhtml+xml; charset=Shift_JIS', true);
            } else if ($agent->isEzweb()) {
                $charset = 'Shift_JIS';
            } else if ($agent->isSoftbank()) {
                $charset = 'UTF-8';
            } else {
                $charset = 'UTF-8';
            }

            $body = $this->getResponse()->getBody();

            // 絵文字に変換
            if ($agent->isEzweb()) {
                $body = str_replace(array('&copy;', '&reg;', '&trade;'), array('&#xE731;', '&#xE736;', '&#xE732;'), $body);
            } else {
                $body = str_replace('&trade;', '&#xE732;', $body);
            }

            // 絵文字変換
            $options = array();
            if (!$agent->isDocomo()) {
                $options[] = 'HexToUtf8';
                $options[] = 'Carrier';
            }
            if ($emoji->isSjisCarrier()) {
                $options[] = 'Utf8ToSjis';
                $body = preg_replace('/(<head>.*charset=)UTF-8(.*<\/head>)/siu', '\1Shift_JIS\2', $body);
            }
            $body = $emoji->filter($body, $options);

            $this->getResponse()->setBody($body);
        }
    }

}
