<?php
/**
 * ファイルサイズを計算する
 *
 * @author  $Author: T_T $
 * @version $Id: Filesize.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_View_Helper_Filesize extends Zend_View_Helper_Abstract
{
    public function filesize($url = null)
    {
        $size = 0;

        if (!is_null($url) && !empty($url)) {
            $_ch = curl_init($url);
            curl_setopt($_ch, CURLOPT_NOBODY, true);
            curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($_ch, CURLOPT_HEADER, true);
            curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($_ch);

            if (preg_match('/Content-Length: (\d+)/', $data, $match)) {
                $size = $match[1];
            }
        }

        return $size;
    }

}
