<?php
class System_Net_IPv4
{
    private $_maskMap = array(0 => '0.0.0.0',
                              1 => '128.0.0.0',
                              2 => '192.0.0.0',
                              3 => '224.0.0.0',
                              4 => '240.0.0.0',
                              5 => '248.0.0.0',
                              6 => '252.0.0.0',
                              7 => '254.0.0.0',
                              8 => '255.0.0.0',
                              9 => '255.128.0.0',
                              10 => '255.192.0.0',
                              11 => '255.224.0.0',
                              12 => '255.240.0.0',
                              13 => '255.248.0.0',
                              14 => '255.252.0.0',
                              15 => '255.254.0.0',
                              16 => '255.255.0.0',
                              17 => '255.255.128.0',
                              18 => '255.255.192.0',
                              19 => '255.255.224.0',
                              20 => '255.255.240.0',
                              21 => '255.255.248.0',
                              22 => '255.255.252.0',
                              23 => '255.255.254.0',
                              24 => '255.255.255.0',
                              25 => '255.255.255.128',
                              26 => '255.255.255.192',
                              27 => '255.255.255.224',
                              28 => '255.255.255.240',
                              29 => '255.255.255.248',
                              30 => '255.255.255.252',
                              31 => '255.255.255.254',
                              32 => '255.255.255.255');

    private $_ip       = '';
    private $_bitmask  = false;
    private $_netmask  = '';
    private $_network  = '';
    private $_broadcast = '';
    private $_long     = 0;

    public function isIp($ip)
    {
        if ($ip == long2ip(ip2long($ip))) {
            return true;
        }

        return false;
    }

    public function isMask($mask)
    {
        if (in_array($mask, $this->_maskMap)) {
            return true;
        }

        return false;
    }

    private function _parseAddress($address)
    {
        if (strchr($address, '/')) {
            $parts = explode('/', $address);
            if (!$this->isIp($parts[0])) {
                throw new Exception('invalid IP address');
            }
            $this->_ip = $parts[0];

            if (preg_match('#^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$#i', $parts[1], $match)) {
                $this->_netmask = hexdec($match[1]) . '.' . hexdec($match[2]) . '.' . hexdec($match[3]) . '.' . hexdec($match[4]);
            } else if (strchr($parts[1], '.')) {
                if (!$this->isMask($parts[1])) {
                    throw new Exception('invalid netmask value');
                }
                $this->_netmask = $parts[1];
            }  else if (ctype_digit($parts[1]) && ($parts[1] >= 0) && ($parts[1] <= 32)) {
                $this->_bitmask = $parts[1];
            } else {
                throw new Exception('invalid netmask value');
            }

            $this->_calculate();
        } else if ($this->isIp($address)) {
            $this->_ip = $address;
        } else {
            throw new Exception('invalid IP address');
        }
    }

    private function _calculate()
    {
        if (strlen($this->_ip)) {
            if (!$this->isIp($this->_ip)) {
                return new Exception('invalid IP address');
            }
            $this->_long = $this->ip2double($this->_ip);
        } else if (is_numeric($this->_long)) {
            $this->_ip = long2ip($this->_loing);
        } else {
            throw new Exception('IP address not specified');
        }

        if (strlen($this->_bitmask)) {
            $this->_netmask = $this->_maskMap[$this->_bitmask];
        } else if (strlen($this->_netmask)) {
            $reverse = array_flip($this->_maskMap);
            $this->_bitmask = $reverse[$this->_netmask];
        } else {
            throw new Exception('netmask or bitmask are required for calculation');
        }

        $this->_network   = long2ip(ip2long($this->_ip) & ip2long($this->_netmask));
        $this->_broadcast = long2ip(ip2long($this->_ip) | (ip2long($this->_netmask) ^ ip2long("255.255.255.255")));
    }

    public function getNetmask($length)
    {
        $this->_parseAddress('0.0.0.0/' . $length);

        return $this->_netmask;
    }

    public function getNetLength($netmask)
    {
        $this->_parseAddress('0.0.0.0/' . $netmask);

        return $this->_bitmask;
    }

    public function getSubnet($ip, $netmask)
    {
        $this->_parseAddress($ip . '/' . $netmask);

        return $this->_network;
    }

    public function ip2double($ip)
    {
        return (double) (sprintf('%u', ip2long($ip)));
    }

    public function ipInNetwork($ip, $network)
    {
        $this->_parseAddress($network);

        $network   = $this->ip2double($this->_network);
        $broadcast = $this->ip2double($this->_broadcast);
        $ip        = $this->ip2double($ip);

        if (($ip >= $network) && ($ip <= $broadcast)) {
            return true;
        }

        return false;
    }

}
