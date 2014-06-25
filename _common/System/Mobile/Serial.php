<?php
/**
 * 携帯の個体識別値取得クラス
 *
 * @author $Author: T_T $
 * @version $Id: Serial.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_Mobile_Serial
{
    public function get()
    {
        require_once 'System/Mobile/Agent.php';

        $agent = new System_Mobile_Agent();

        if ($agent->isDocomo()) {
            return $this->getDocomo();
        } else if ($agent->isEzweb()) {
            return $this->getEzweb();
        } else if ($agent->isSoftbank()) {
            return $this->getSoftbank();
        } else if ($agent->isWillcom()) {
            return $this->getWillcom();
        } else if ($agent->isEmobile()) {
            return $this->getEmobile();
        }

        return '';
    }

    public function getDocomo()
    {
        if (isset($_SERVER['HTTP_X_DCMGUID'])) {
            return $_SERVER['HTTP_X_DCMGUID'];
        }

        return '';
    }

    public function getEzweb()
    {
        if (isset($_SERVER['HTTP_X_UP_SUBNO'])) {
            return $_SERVER['HTTP_X_UP_SUBNO'];
        }

        return '';
    }

    public function getSoftbank()
    {
        if (isset($_SERVER['HTTP_X_JPHONE_UID'])) {
            return $_SERVER['HTTP_X_JPHONE_UID'];
        } else if (preg_match('|^.+/SN(\w+).*$|', $_SERVER['HTTP_USER_AGENT'], $match)) {
            return $match[1];
        }

        return '';
    }

    public function getWillcom()
    {
        return '';
    }

    public function getEmobile()
    {
        if (isset($_SERVER['HTTP_X_EM_UID'])) {
            return $_SERVER['HTTP_X_EM_UID'];
        }

        return '';
    }

}
