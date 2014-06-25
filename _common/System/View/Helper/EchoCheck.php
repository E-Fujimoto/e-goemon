<?php
/**
 * 配列中のキーの存在を調べ、ある場合は checked="checked" を返す
 *
 * @author  $Author: T_T $
 * @version $Id: EchoCheck.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_EchoCheck extends Zend_View_Helper_Abstract
{
    public function echoCheck($array, $key, $value)
    {
        $str = '';

        if (!is_null($array) && is_array($array) && !is_null($key)) {
            if (array_key_exists($key, $array)) {
                if (!is_array($array[$key])) {
                    $data = array($array[$key]);
                } else {
                    $data = $array[$key];
                }

                if (in_array($value, $data)) {
                    $str = ' checked="checked"';
                }
            }
        }

        return $str;
    }

}
