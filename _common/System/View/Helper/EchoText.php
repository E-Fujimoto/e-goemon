<?php
/**
 * 配列中のキーの存在を調べ、ある場合は返す
 *
 * @author  $Author: T_T $
 * @version $Id: EchoText.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_EchoText extends Zend_View_Helper_Abstract
{
    public function echoText($array, $key)
    {
        $str = '';

        if (!is_null($array) && is_array($array) && !is_null($key)) {
            if (array_key_exists($key, $array)) {
                $str = $array[$key];
            }
        }

        return $str;
    }

}
