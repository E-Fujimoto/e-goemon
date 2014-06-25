<?php
/**
 * メールアドレスのキャリア判定クラス
 *
 * @author $Author: T_T $
 * @version $Id: Address.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_Mobile_Address
{
    public function isMobile($email = null)
    {
        if ($this->isDocomo($email)) {
            return true;
        } else if ($this->isEzweb($email)) {
            return true;
        } else if ($this->isSoftbank($email)) {
            return true;
        } else if ($this->isWillcom($email)) {
            return true;
        } else if ($this->isEmobile($email)) {
            return true;
        }

        return false;
    }

    public function isPc($email = null)
    {
        return !$this->isMobile($email);
    }

    public function isDocomo($email = null)
    {
        if (preg_match('/@docomo\.ne\.jp$/', $email)) {
            return true;
        }

        return false;
    }

    public function isEzweb($email = null)
    {
        if (preg_match('/@ezweb\.ne\.jp$/', $email)) {
            return true;
        }

        return false;
    }

    public function isSoftbank($email = null)
    {
        if (preg_match('/@softbank\.ne\.jp$/', $email)) {
            return true;
        } else if (preg_match('/@i\.softbank\.jp$/', $email)) {
            return true;
        } else if (preg_match('/@disney\.ne\.jp$/', $email)) {
            return true;
        } else if (preg_match('/@[dhtcrknsq]\.vodafone\.ne\.jp$/', $email)) {
            return true;
        }

        return false;
    }

    public function isWillcom($email = null)
    {
        if (preg_match('/@pdx\.ne\.jp$/', $email)) {
            return true;
        } else if (preg_match('/@(di|dj|dk|wm)\.pdx\.ne\.jp$/', $email)) {
            return true;
        } else if (preg_match('/@wilcom\.com$/', $email)) {
            return true;
        }

        return false;
    }

    public function isEmobile($email = null)
    {
        if (preg_match('/@emnet\.ne\.jp$/', $email)) {
            return true;
        }

        return false;
    }

}
