<?php
class System_Log_Db extends System_Log_LogAbstract
{
    private $_db = null;

    private $_table = '';

    private $_colums = array();

    public function __construct()
    {
        $this->_db = Zend_Db_Table::getDefaultAdapter();

        $this->_table   = 'log';
        $this->_columns = array('priority'      => 'priority',
                                'priority_name' => 'priorityName',
                                'message'       => 'message',
                                'event_date'    => 'timestamp',
                                'ip_address'    => 'ip',
                                'remote_host'   => 'host',
                                'user_agent'    => 'user-agent');

        $this->setWriter();
        $this->setLogger();
    }

    public function setWriter()
    {
        if (!is_null($this->_db) && $this->_db instanceof Zend_Db_Adapter_Abstract &&
            !empty($this->_table) && !empty($this->_columns)) {

            $this->_writer = new Zend_Log_Writer_Db($this->_db, $this->_table, $this->_columns);
        }

        return $this;
    }

    public function setLogger()
    {
        if (!is_null($this->_writer) && $this->_writer instanceof Zend_Log_Writer_Abstract) {
            $this->_logger = new Zend_Log($this->_writer);
            $this->setEventItem();
        }

        return $this;
    }

}
