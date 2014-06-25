<?php
class System_Controller_Action_Helper_Format extends Zend_Controller_Action_Helper_Abstract
{
    public function snakeToCamel($str, $delimiter = '_')
    {
        if (strpos($str, $delimiter)) {
            $str = str_replace($delimiter, ' ', $str);
            $str = ucwords($str);
            $str = str_replace(' ', '', $str);
            $str = lcfirst($str);
        }

        return $str;
    }

    public function camelToSnake($str, $delimiter = '_')
    {
        if (!strpos($str, $delimiter)) {
            $str = preg_replace('/([A-Z])/', '_$1', $str);
            $str = strtolower($str);
            $str = ltrim($str, '_');
        }

        return $str;
    }

    public function direct($str, $delimiter)
    {
        return $this->camelFromSnake($str, $delimiter);
    }


}
