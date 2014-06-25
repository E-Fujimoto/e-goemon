<?php
abstract class System_Log_LogAbstract
{
    protected $_writer = null;

    protected $_logger = null;

    abstract public function setWriter();

    abstract public function setLogger();

    public function setEventItem()
    {
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $remoteHost = isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '';
        $userAgent  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $this->_logger->setEventItem('ip', $remoteAddr);
        $this->_logger->setEventItem('host', $remoteHost);
        $this->_logger->setEventItem('user-agent', $userAgent);

        return $this;
    }

    public function log($message, $priority = 7)
    {
        $this->_logger->log($message, $priority);

        return $this;
    }

    public function emerg($message)
    {
        return $this->log($mesage, 0);
    }

    public function alert($message)
    {
        return $this->log($message, 1);
    }

    public function crit($message)
    {
        return $this->log($message, 2);
    }

    public function err($message)
    {
        return $this->log($message, 3);
    }

    public function warn($message)
    {
        return $this->log($message, 4);
    }

    public function notice($message)
    {
        return $this->log($message, 5);
    }

    public function info($message)
    {
        return $this->log($message, 6);
    }

    public function debug($message)
    {
        return $this->log($message, 7);
    }

}