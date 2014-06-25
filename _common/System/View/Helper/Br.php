<?php
/**
 * 改行コードを HTML の改行へ直す
 *
 * このヘルパーでは値がない場合、空白の実体参照を返す
 *
 * @author $Author: T_T $
 * @version $Id: Br.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_Br extends Zend_View_Helper_Abstract
{
    public function br($str = null)
    {
        if (is_null($str) || empty($str)) {
            $str = '&nbsp;';
        } else {
            $str = htmlspecialchars($str, ENT_QUOTES);
        }

        return nl2br($str);
    }

}
