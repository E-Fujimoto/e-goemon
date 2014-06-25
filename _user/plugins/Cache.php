<?php
class Plugin_Cache extends Zend_Controller_Plugin_Abstract
{
    // ページキャッシュオブジェクト取得
    private function _getPageCache()
    {
        $cache= Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                    ->getResource('CacheManager')
                                                    ->getCache('database');

        return $cache;
    }

    // データキャッシュオブジェクト取得
    private function _getCoreCache()
    {
        $cache= Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                    ->getResource('CacheManager')
                                                    ->getCache('core');

        return $cache;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // キャッシュオブジェクト取得
        $pageCache = $this->_getPageCache();
        $coreCache = $this->_getCoreCache();

        // テーブルのメタデータをキャッシュ
        Zend_Db_Table_Abstract::setDefaultMetadataCache($coreCache);

        // Zend_Date をキャッシュ
        Zend_Date::setOptions(array('cache' => $coreCache));

        // キャッシュオブジェクトをレジストリに登録
        Zend_Registry::set('page_cache', $pageCache);
        Zend_Registry::set('core_cache', $coreCache);

        // キャッシュの prefix として dbname を取得する
        $dbConfig = Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                        ->getResource('db')
                                                        ->getConfig();

        Zend_Registry::set('dbname', $dbConfig['dbname']);

        // POST 処理が行われた場合、キャッシュをクリア
        if ($request->isPost() && in_array($request->getActionName(), array('add', 'edit', 'active', 'passive', 'recalc-sales'))) {
            $coreCache->clean();
            $pageCache->clean();
        }
        if ($request->getControllerName() == 'logout') {
            $pageCache->clean();
            $coreCache->clean();
        }
    }

}
