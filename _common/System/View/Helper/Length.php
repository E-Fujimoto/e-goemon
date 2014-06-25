<?php
/**
 * 配列中のキーの存在を調べ、ある場合は値の長さを返す
 *
 * @author  $Author: T_T $
 * @version $Id: Length.php 25 2010-03-11 04:56:01Z T_T $
 */
class System_View_Helper_Length extends Zend_View_Helper_Abstract
{
    public function length($array, $key)
    {
        $str = '';

        if (!is_null($array) && is_array($array) && !is_null($key)) {
            if (array_key_exists($key, $array)) {
                $str = $array[$key];
            }
        }

        return mb_strlen($str, mb_internal_encoding());
    }

}
