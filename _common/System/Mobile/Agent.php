<?php
/**
 * 携帯のユーザーエージェント判定クラス
 *
 * @author $Author: T_T $
 * @version $Id: Agent.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_Mobile_Agent
{
    public function getName($userAgent = null)
    {
        if ($this->isDocomo($userAgent)) {
            return 'DoCoMo';
        } else if ($this->isEzweb($userAgent)) {
            return 'EZweb';
        } else if ($this->isSoftbank($userAgent)) {
            return 'SoftBank';
        } else if ($this->isWillcom($userAgent)) {
            return 'Willcom';
        } else if ($this->isEmobile($userAgent)) {
            return 'emobile';
        } else if ($this->isIphone($userAgent)) {
            return 'iPhone';
        } else if ($this->isAndroid($userAgent)) {
            return 'Android';
        } else {
            return 'pc';
        }
    }

    public function isMobile($userAgent = null)
    {
        if ($this->isDocomo($userAgent)) {
            return true;
        } else if ($this->isEzweb($userAgent)) {
            return true;
        } else if ($this->isSoftbank($userAgent)) {
            return true;
        } else if ($this->isWillcom($userAgent)) {
            return true;
        } else if ($this->isEmobile($userAgent)) {
            return true;
        }

        return false;
    }

    public function isSmartphone($userAgent = null)
    {
        if ($this->isIphone($userAgent)) {
            return true;
        } else if ($this->isAndroid($userAgent)) {
            return true;
        }

        return false;
    }

    public function isPc($userAgent = null)
    {
        return !$this->isMobile($userAgent);
    }

    public function isDocomo($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('/^DoCoMo/', $userAgent)) {
            return true;
        }

        return false;
    }

    public function isEzweb($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('/^KDDI/', $userAgent)) {
            return true;
        } else if (preg_match('/^UP\.Browser/', $userAgent)) {
            return true;
        }

        return false;
    }

    public function isSoftbank($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('/^SoftBank/', $userAgent)) {
            return true;
        } else if (preg_match('/^Semulator/', $userAgent)) {
            return true;
        } else if (preg_match('/^Vodafone/', $userAgent)) {
            return true;
        } else if (preg_match('/^MOT-/', $userAgent)) {
            return true;
        } else if (preg_match('/^MOTEMULATOR/', $userAgent)) {
            return true;
        } else if (preg_match('/^J-PHONE/', $userAgent)) {
            return true;
        } else if (preg_match('/^J-EMULATOR/', $userAgent)) {
            return true;
        }

        return false;
    }

    public function isWillcom($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('!^Mozilla/3\.0\((?:DDIPOCKET|WILLCOM);!', $userAgent)) {
            return true;
        }

        return false;
    }

    public function isEmobile($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        $emobile = array('H11T', 'H11HW', 'H12HW', 'S11HT', 'S12HT', 'S21HT', 'S22HT');

        foreach ($emobile as $val) {
            if (strpos($userAgent, $val) !== false) {
                return true;
            }
        }

        return false;
    }

    public function isIphone($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('!^Mozilla/5\.0 \((iPhone|iPod);!', $userAgent)) {
            return true;
        }

        return false;
    }

    public function isAndroid($userAgent = null)
    {
        if (is_null($userAgent) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        if (preg_match('!^.+Android.+$!', $userAgent)) {
            return true;
        }

        return false;
    }

}
