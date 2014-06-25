<?php
/**
 * 指定された文字列を先頭から指定された文字数までで切り取り、指定した文字を付与して返す（マルチバイト対応）
 *
 * @author  $Author: m_w $
 * @version $Id:$
 * @param string  $str        対象文字列
 * @param integer $length     丸める幅（文字数）
 * @param string  $trimmarker 丸めた後にその文字列の最後に追加される文字列
 */
class System_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    public function truncate($str, $length, $join = '')
    {
        if (mb_strlen($str) > $length) {
            $str = mb_substr($str, 0, $length) . $join;
        }

        return $str;
    }

}
