<?php
/**
 *  サービスクラスの基底クラス
 *
 *  @category  System
 *  @package   System_Service
 *  @copyright Copyright 2014 SDM-Summit
 *  @license   New BSD License
 *  @version   $Id$
 */
abstract class System_Service_ServiceAbstract
{
    protected $_db = null;

    protected $_table = '';

    /**
     * コンストラクタ
     *
     * 継承したクラスでは、このコンストラクタを実行した後、テーブル名を設定すること
     */
    public function __construct()
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * select文作成用のオブジェクトを生成する
     *
     * @return System_Db_Select
     */
    public function select()
    {
        return new System_Db_Select($this->_db);
    }

    /**
     * トランザクションを利用してデータを1行追加する
     *
     * @param array $data 追加するデータの連想配列
     * @return null|int 追加したデータのauto_increment値
     */
    public function insert($data)
    {
        $id = null;

        try {
            $this->_db->beginTransaction();
            $this->_db->insert($this->_table, $data);
            $id = $this->_db->lastInsertId();
            if (empty($id)) {
                $id = true;
            }
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            $this->_setDbError($e);
        }

        return $id;
    }

    /**
     * トランザクションを使用してデータを修正する
     *
     * @param array $data 編集するデータの連想配列
     * @param array $where 編集対象の指定句の配列
     * @return boolean 編集結果の成否
     */
    public function update($data, $where)
    {
        try {
            $this->_db->beginTransaction();
            $this->_db->update($this->_table, $data, $where);
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            $this->_setDbError($e);

            return false;
        }

        return true;
    }

    /**
     * トランザクションを使用してデータを削除する
     *
     * @param array $where 削除対象の指定句の配列
     * @return boolean 削除結果の成否
     */
    public function delete($where)
    {
        try {
            $this->_db->beginTransaction();
            $this->_db->delete($this->_table, $where);
            $this->_db->commit();
        } catch (Exception $e) {
            $this->_db->rollBack();
            $this->_setDbError($e);

            return false;
        }

        return true;
    }

    public function fetchRow($select, $bind = array(), $fetchMode = null)
    {
        return $this->_db->fetchRow($select, $bind, $fetchMode);
    }

    public function fetchAll($select, $bind = array(), $fetchMode = null)
    {
        return $this->_db->fetchAll($select, $bind, $fetchMode);
    }

    public function fetchOne($select, $bind = array())
    {
        return $this->_db->fetchOne($select, $bind);
    }

    public function fetchPairs($select, $bind = array())
    {
        return $this->_db->fetchPairs($select, $bind);
    }

    public function fetchCol($select, $bind = array())
    {
        return $this->_db->fetchCol($select, $bind);
    }

    public function fetchAssoc($select, $bind = array())
    {
        return $this->_db->fetchAssoc($select, $bind);
    }

    public function quote($value, $type = null)
    {
        return $this->_db->quote($value, $type);
    }

    public function quoteInto($text, $value, $type = null, $count = null)
    {
        return $this->_db->quoteInto($text, $value, $type, $count);
    }

    public function quoteIdentifier($ident, $auto = false)
    {
        return $this->_db->quoteIdentifier($ident, $auto);
    }

    public function quoteColumnAs($ident, $alias, $auto = false)
    {
        return $this->_db->quoteColumnAs($ident, $alias, $auto);
    }

    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        return $this->_db->quoteTableAs($ident, $alias, $auto);
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
