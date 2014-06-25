<?php
class System_View_Helper_Error extends Zend_View_Helper_Abstract
{
    private $_errors = array();

    public function error($field = null)
    {
        if (empty($this->_errors)) {
            $this->_errors = Zend_Controller_Action_HelperBroker::getStaticHelper('error')->getAll();
        }

        return $this;
    }

    public function exists($field = null)
    {
        if ($field === null) {
            return empty($this->_errors) ? false : true;
        } else if (isset($this->_errors[$field])) {
            return empty($this->_errors[$field]) ? false : true;
        }

        return false;
    }

    public function get($field = null)
    {
        if ($field === null) {
            $data = $this->_errors;
        } else {
            if (isset($this->_errors[$field])) {
                $data = $this->_errors[$field];
            } else {
                $data = array();
            }
        }

        return $data;
    }

    public function getList($default = true)
    {
        $errors = array();
        if (!empty($this->_errors)) {
            foreach ($this->_errors as $key => $val) {
                if (is_array($val)) {
                    if ($default) {
                        foreach ($val as $k => $v) {
                            $errors[] = $v;
                        }
                    }
                } else {
                    $errors[] = $val;
                }
            }
        }

        return $errors;
    }

}
