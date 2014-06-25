<?php
/**
 * 数値を 3 桁区切りに変更する
 *
 * このヘルパーでは 0 をそのまま出力する
 *
 * @author $Author: T_T $
 * @version $Id: Money.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_Money extends Zend_View_Helper_Abstract
{
    public function money($num = null)
    {
        return htmlspecialchars(number_format($num, 0, '.', ','), ENT_QUOTES);
    }

}
