<?php
class System_Net_Ftp_Observer
{
    private $_id;

    public function __construct()
    {
        $this->_id = md5(microtime());
    }

    public function getId()
    {
        return $this->_id;
    }

    public function notify($event)
    {
        return;
    }

}
