<?php
/**
 * 数値を 3 桁区切りに変更する
 *
 * このヘルパーでは 0 を &nbsp; として出力する
 *
 * @author $Author: T_T $
 * @version $Id: Num.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_Num extends Zend_View_Helper_Abstract
{
    public function num($num = null)
    {
        if (is_null($num) || empty($num)) {
            $num = '&nbsp;';
        } else {
            $num = htmlspecialchars(number_format($num, 0, '.', ','), ENT_QUOTES);
        }

        return $num;
    }

}
