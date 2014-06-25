<?php
abstract class System_Service_ServiceAbstract
{
    protected $_db = null;

    public function getDao($name)
    {
        $dao = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get($name);

        if ($dao instanceof Zend_Db_Table_Abstract) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();

            if (!($this->_db instanceof Zend_Db_Adapter_Abstract)) {
                // エラーメッセージの取得
                if (isset(Zend_Registry::getInstance()->get('config')->error->db)) {
                    $error = Zend_Registry::getInstance()->get('config')->error->db;
                } else {
                    $error = 'エラーが発生しました';
                }

                throw new Exception($error);
            }
        }

        return $dao;
    }

    /**
     * データベースエラーのログを取る
     *
     * エラーとして表示させることはないためログを取る
     *
     * @param  object $exception Exceptionクラスのオブジェクト
     * @return void
     */
    protected function _setDbError($exception)
    {
        // Exception のメッセージをログテーブルに格納する
        $str = "[Messsage]\n"
            . $exception->getMessage()
            . "\n\n"
            . "[Trace]\n"
            . $exception->getTraceAsString();

        $log = Zend_Controller_Action_HelperBroker::getStaticHelper('resource')->get('system_log_db');
        $log->log($str, 0);

        // エラーメッセージの取得
        if (isset(Zend_Registry::getInstance()->get('config')->error->db)) {
            $error = Zend_Registry::getInstance()->get('config')->error->db;
        } else {
            $error = 'エラーが発生しました';
        }

        // エラーメッセージをエラーヘルパーに格納
        Zend_Controller_Action_HelperBroker::getStaticHelper('error')->set('db', $error);
    }

}
