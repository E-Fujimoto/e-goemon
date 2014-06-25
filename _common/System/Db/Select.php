<?php
require_once 'Zend/Db/Select.php';

class System_Db_Select extends Zend_Db_Select
{
    const USE_INDEX       = 'useIndex';
    const FORCE_INDEX     = 'forceIndex';
    const SQL_USE_INDEX   = 'USE INDEX';
    const SQL_FORCE_INDEX = 'FORCE INDEX';

    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_parts[self::USE_INDEX]   = array();
        $this->_parts[self::FORCE_INDEX] = array();

        parent::__construct($adapter);
    }

    /**
     * Override parent method
     */
    protected function _renderFrom($sql)
    {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        if (empty($this->_parts[self::FROM])) {
            $this->_parts[self::FROM] = $this->_getDummyTable();
        }

        $from = array();

        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            $tmp = '';

            $joinType = ($table['joinType'] == self::FROM) ? self::INNER_JOIN : $table['joinType'];

            // Add join clause (if applicable)
            if (! empty($from)) {
                $tmp .= ' ' . strtoupper($joinType) . ' ';
            }

            $tmp .= $this->_getQuotedSchema($table['schema']);
            $tmp .= $this->_getQuotedTable($table['tableName'], $correlationName);

            // Add use index statement after FROM, before joins (if applicable)
            if(!empty($this->_parts[self::USE_INDEX])) {
                $tmp .= ' ' . self::SQL_USE_INDEX . '(' . implode(',', $this->_parts[self::USE_INDEX]) . ')';
                unset($this->_parts[self::USE_INDEX]);
            }
            if(!empty($this->_parts[self::FORCE_INDEX])) {
                $tmp .= ' ' . self::SQL_FORCE_INDEX . '(' . implode(',', $this->_parts[self::FORCE_INDEX]) . ')';
                unset($this->_parts[self::FORCE_INDEX]);
            }

            // Add join conditions (if applicable)
            if (!empty($from) && ! empty($table['joinCondition'])) {
                $tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCondition'];
            }

            // Add the table name and condition add to the list
            $from[] = $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
        }

        return $sql;
    }

    /**
     * Specify index to use
     *
     * @return Zend_Db_Select
     */
    public function useIndex($index)
    {
        if (empty($this->_parts[self::FORCE_INDEX])) {
            if (!is_array($index)) {
                $index = array($index);
            }
            $this->_parts[self::USE_INDEX] = $index;

            return $this;
        } else {
            throw new Zend_Db_Select_Exception("Cannot use 'USE INDEX' in the same query as 'FORCE INDEX'");
        }
    }

    /**
     * Force index to use
     *
     * @return Zend_Db_Select
     */
    public function forceIndex($index)
    {
        if (empty($this->_parts[self::USE_INDEX])) {
            if(!is_array($index)) {
                $index = array($index);
            }
            $this->_parts[self::FORCE_INDEX] = $index;

            return $this;
        } else {
            throw new Zend_Db_Select_Exception("Cannot use 'FORCE INDEX' in the same query as 'USE INDEX'");
        }
    }

}
