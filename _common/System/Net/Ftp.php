<?php
class System_Net_Ftp
{
    private $_code = array('NET_FTP_FILES_ONLY'  => 0,
                           'NET_FTP_DIRS_ONLY'   => 1,
                           'NET_FTP_DIRS_FILES'  => 2,
                           'NET_FTP_RAWLIST'     => 3,
                           'NET_FTP_BLOCKING'    => 1,
                           'NET_FTP_NONBLOCKING' => 2);

    private $_param = array('host'    => '',
                            'port'    => 21,
                            'user'    => '',
                            'pass'    => '',
                            'passive' => false,
                            'mode'    => FTP_BINARY,
                            'timeout' => 100);

    private $_ftp = null;

    private $_ext = array();

    private $_lsMatch = null;

    private $_matcher = null;

    private $_listeners = array();

    public function __construct($param = array())
    {
        if (!empty($param)) {
            $this->setParam($param);
        }

        $this->_lsMatch = array('unix'    => array('pattern' => '/(?:(d)|.)([rwxts-]{9})\s+(\w+)\s+([\w\d-()?.]+)\s+([\w\d-()?.]+)\s+(\w+)\s+(\S+\s+\S+\s+\S+)\s+(.+)/',
                                                   'map'     => array('is_dir'        => 1,
                                                                      'rights'        => 2,
                                                                      'files_inside'  => 3,
                                                                      'user'          => 4,
                                                                      'group'         => 5,
                                                                      'size'          => 6,
                                                                      'date'          => 7,
                                                                      'name'          => 8)),
                                'windows' => array('pattern' => '/([0-9\-]+)\s+([0-9:APM]+)\s+((<DIR>)|\d+)\s+(.+)/',
                                                   'map'     => array('date'   => 1,
                                                                      'time'   => 2,
                                                                      'size'   => 3,
                                                                      'is_dir' => 4,
                                                                      'name'   => 5)));
    }

    public function setParam($param)
    {
        if (isset($param['host'])) {
            $this->setHost($param['host']);
        }
        if (isset($param['user'])) {
            $this->setUser($param['user']);
        }
        if (isset($param['pass'])) {
            $this->setPass($param['pass']);
        }

        $param['port'] = isset($param['port']) ? $param['port'] : 21;
        $this->setPort($param['port']);

        $param['passive'] = isset($param['passive']) ? $param['passive'] : false;
        $this->setPassive($param['passive']);

        $param['mode'] = isset($param['mode']) ? $param['mode'] : FTP_BINARY;
        $this->setMode($param['mode']);

        $param['timeout'] = isset($param['timeout']) ? $param['timeout'] : 100;
        $this->setTimeout($param['timeout']);

        return $this;
    }

    public function getParam()
    {
        return $this->_param;
    }

    public function setHost($host)
    {
        $this->_param['host'] = $host;

        return $this;
    }

    public function setPort($port)
    {
        $this->_param['port'] = $port;

        return $this;
    }

    public function setUser($user)
    {
        $this->_param['user'] = $user;

        return $this;
    }

    public function setPass($pass)
    {
        $this->_param['pass'] = $pass;

        return $this;
    }

    public function setPassive($passive)
    {
        $this->_param['passive'] = $passive;

        return $this;
    }

    public function setMode($mode)
    {
        $this->_param['mode'] = $mode;

        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->_param['timeout'] = $timeout;

        return $this;
    }

    public function connect($param = array())
    {
        $this->_matcher = null;

        if (!empty($param)) {
            $this->setParam($param);
        }

        $this->_ftp = @ftp_connect($this->_param['host'], $this->_param['port'], $this->_param['timeout']);

        if (!$this->_ftp) {
            return false;
        }

        return true;
    }

    public function disconnect()
    {
        $result = @ftp_close($this->_ftp);

        if (!$result) {
            return false;
        }

        return true;
    }

    public function login($user = '', $pass = '', $passive = null)
    {
        if (is_null($this->_ftp)) {
            $this->connect();
        }
        if (!empty($user)) {
            $this->setUser($user);
        }
        if (!empty($pass)) {
            $this->setPass($pass);
        }
        if (!is_null($passive)) {
            $this->setPassive($passive);
        }

        $result = @ftp_login($this->_ftp, $this->_param['user'], $this->_param['pass']);

        if (!$result) {
            return false;
        }

        $this->passive();

        return true;
    }

    public function passive($passive = null)
    {
        if (!is_null($passive)) {
            $this->setPassive($passive);
        }

        $result = @ftp_pasv($this->_ftp, $this->_param['passive']);

        if (!$result) {
            return false;
        }

        return true;
    }

    public function cd($dir)
    {
        $result = @ftp_chdir($this->_ftp, $dir);

        if (!$result) {
            return false;
        }

        return true;
    }

    public function pwd()
    {
        $result = @ftp_pwd($this->_ftp);

        if (!$result) {
            throw new Exception('Could not determine the actual path');
        }

        return $result;
    }

    public function mkdir($dir, $recursive = false)
    {
        $dir     = $this->_constructPath($dir);
        $saveDir = $this->pwd();

        if ($this->cd($dir)) {
            if ($this->cd($saveDir)) {
                return true;
            }
        }

        $this->cd($saveDir);

        if (!$recursive) {
            $result = @ftp_mkdir($this->_ftp, $dir);

            if (!$result) {
                return false;
            }

            return true;
        } else {
            if (!strpos(substr($dir, 1), '/')) {
                return $this->mkdir($dir, false);
            }
            if (substr($dir, -1) == '/') {
                $dir = substr($dir, 0, -1);
            }
            $parent = substr($dir, 0, strrpos($dir, '/'));
            $result = $this->mkdir($parent, true);
            if ($result) {
                $result = $this->mkdir($dir, false);
            }
            if (!$result) {
                return false;
            }

            return true;
        }
    }

    private function _constructPath($path)
    {
        if ((substr($path, 0, 1) != '/') && (substr($path, 0, 2) != './')) {
            $actualDir = @ftp_pwd($this->_ftp);

            if (substr($actualDir, -1) != '/') {
                $actualDir .= '/';
            }

            $path = $actualDir . $path;
        }

        return $path;
    }

    public function execute($command)
    {
        $result = @ftp_exec($this->_ftp, $command);

        if (!$result) {
            return false;
        }

        return true;
    }

    public function site($command)
    {
        $result = @ftp_site($this->_ftp, $command);

        if (!$result) {
            return false;
        }

        return true;
    }

    public function chmod($target, $permission)
    {
        if (is_array($target)) {
            $count = count($target);
            for ($i = 0; $i < $count; $i++) {
                $result = $this->chmod($target[$i], $permission);
                if (!$result) {
                    return false;
                }
            }
        } else {
            $result = $this->site('CHMOD ' . $permission . ' ' . $target);
            if (!$result) {
                return false;
            }

            return true;
        }
    }

    public function chmodRecursive($target, $permission)
    {
        static $dirPermission;

        if (!isset($dirPermission)) {
            $dirPermission = $this->_makeDirPermissions($permission);
        }

        if (is_array($target)) {
            $count = count($target);
            for ($i = 0; $i < $count; $i++) {
                $result = $this->chmodRecursive($target[$i], $permission);
                if (!$result) {
                    return false;
                }
            }
        } else {
            $remotePath = $this->_constructPath($target);

            $result = $this->chmod($remotePath, $dirPermission);

            if (!$result) {
                return false;
            }

            if (substr($remotePath, strlen($remotePath) - 1) != '/') {
                $remotePath .= '/';
            }

            $dirList = $this->ls($remotePath, $this->_code['NET_FTP_DIRS_ONLY']);
            foreach ($dirList as $dirEntry) {
                if ($dirEntry['name'] == '.' || $dirEntry['name'] == '..') {
                    continue;
                }

                $remotePathNew = $remotePath . $dirEntry['name'] . '/';

                $result = $this->chmod($remotePathNew, $dirPermission);
                if (!$result) {
                    return false;
                }

                $result = $this->chmodRecursive($remotePathNew, $permission);
                if (!$result) {
                    return false;
                }
            }

            $fileList = $this->ls($remotePath, $this->_code['NET_FTP_FILES_ONLY']);
            foreach ($fileList as $fileEntry) {
                $remoteFile = $remotePath . $fileEntry['name'];

                $result = $this->chmod($remoteFile, $permission);
                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    private function _makeDirPermissions($permission)
    {
        $permission = (string) $permission;

        $count = strlen($permission);
        for ( $i = 0; $i < $count; $i++) {
            if ((int) $permission{$i} & 4 && !((int) $permission{$i} & 1)) {
                (int) $permission{$i} = (int) $permission{$i} + 1;
            }
        }

        return (string) $permission;
    }

    public function rename($remoteFrom, $remoteTo)
    {
        $result = @ftp_rename($this->_ftp, $remoteFrom, $remoteTo);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function mdtm($file, $format = null)
    {
        $file = $this->_constructPath($file);
        if (!$this->_checkRemoteDir($file)) {
            return false;
        }

        $result = @ftp_mdtm($this->_ftp, $file);
        if ($result == -1) {
            return false;
        }

        if (isset($format)) {
            $result = date($format, $result);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    private function _checkRemoteDir($path)
    {
        $pwd = $this->pwd();

        $result = $this->cd($path);
        $this->cd($pwd);

        return $result;
    }

    public function size($file)
    {
        $file = $this->_constructPath($file);
        $result = @ftp_size($this->_ftp, $file);
        if ($result == -1) {
            return false;
        }

        return $result;
    }

    public function ls($dir = null, $mode = 'NET_FTP_DIRS_FILES')
    {
        if (!isset($dir)) {
            $dir = @ftp_pwd($this->_ftp);
            if (!$dir) {
                return false;
            }
        }
        if (($mode != 'NET_FTP_FILES_ONLY') && ($mode != 'NET_FTP_DIRS_ONLY') && ($mode != 'NET_FTP_RAWLIST')) {
            $mode = 'NET_FTP_DIRS_FILES';
        }

        $result = false;
        switch ($mode) {
            case 'NET_FTP_DIRS_FILES':
                $result = $this->_lsBoth($dir);
                break;
            case 'NET_FTP_DIRS_ONLY':
                $result = $this->_lsDirs($dir);
                break;
            case 'NET_FTP_FILES_ONLY':
                $result = $this->_lsFiles($dir);
                break;
            case 'NET_FTP_RAWLIST':
                $result = @ftp_rawlist($this->_ftp, $dir);
                break;
        }

        return $result;
    }

    private function _lsBoth($dir)
    {
        $listSplitted = $this->_listAndParse($dir);
        if (!$listSplitted) {
            return false;
        }
        if (!is_array($listSplitted['files'])) {
            $listSplitted['files'] = array();
        }
        if (!is_array($listSplitted['dirs'])) {
            $listSplitted['dirs'] = array();
        }

        $result = array();
        @array_splice($result, 0, 0, $listSplitted['files']);
        @array_splice($result, 0, 0, $listSplitted['dirs']);

        return $result;
    }

    private function _lsDirs($dir)
    {
        $list = $this->_listAndParse($dir);
        if (!$list) {
            return false;
        }

        return $list['dirs'];
    }

    private function _lsFiles($dir)
    {
        $list = $this->_listAndParse($dir);
        if (!$list) {
            return false;
        }

        return $list['files'];
    }

    private function _listAndParse($dir)
    {
        $dirsList  = array();
        $filesList = array();
        $dirList   = @ftp_rawlist($this->_ftp, $dir);
        if (!is_array($dirList)) {
            return false;
        }

        foreach ($dirList as $key => $val) {
            if (strncmp($val, 'total: ', 7) == 0 && preg_match('/total: \d+/', $val)) {
                unset($dirList[$key]);
                break;
            }
        }

        if (count($dirList) == 0) {
            return array('dirs' => $dirsList, 'files' => $filesList);
        }

        if (count($dirsList) == 1 && $dirsList[0] == 'total 0') {
            return array('dirs' => array(), 'files' => $filesList);
        }

        if (!isset($this->_matcher) || $this->_matcher === false) {
            $this->_matcher = $this->_determineOSMatch($dirList);
            if (!$this->_matcher) {
                return $this->_matcher;
            }
        }

        foreach ($dirList as $entry) {
            if (!preg_match($this->_matcher['pattern'], $entry, $match)) {
                continue;
            }

            $entry = array();
            foreach ($this->_matcher['map'] as $key => $val) {
                $entry[$key] = $match[$val];
            }
            $entry['stamp'] = $this->_parseDate($entry['date']);

            if ($entry['is_dir']) {
                $dirsList[] = $entry;
            } else {
                $filesList[] = $entry;
            }
        }

        @usort($dirsList, array('System_Net_Ftp', 'natSort'));
        @usort($filesList, array('System_Net_Ftp', 'natSort'));
        $result['dirs']  = is_array($dirsList) ? $dirsList : array();
        $result['files'] = is_array($filesList) ? $filesList : array();

        return $result;
    }

    private function _determineOSMatch($dirList)
    {
        foreach ($dirList as $entry) {
            foreach ($this->_lsMatch as $os => $match) {
                if (preg_match($match['pattern'], $entry)) {
                    return $match;
                }
            }
        }

        return false;
    }

    private function _parseDate($date)
    {
        if (preg_match('/([A-Za-z]+)[ ]+([0-9]+)[ ]+([0-9]+):([0-9]+)/', $date, $match)) {
            $year    = date('Y');
            $month   = $match[1];
            $day     = $match[2];
            $hour    = $match[3];
            $minute  = $match[4];
            $date    = "$month $day, $year $hour:$minute";
            $tmpDate = strtotime($date);
            if ($tmpDate > time()) {
                $year--;
                $date = "$month $day, $year $hour:$minute";
            }
        } else if (preg_match('/^\d\d-\d\d-\d\d/', $date)) {
            $date = str_replace('-', '/', $date);
        }

        $result = strtotime($date);
        if (!$result) {
            return false;
        }


        return $result;
    }

    public function rm($path, $recursive = false, $fileOnly = false)
    {
        $path = $this->_constructPath($path);
        if ($this->_checkRemoteDir($path)) {
            if ($recursive) {
                return $this->_rmDirRecursive($path, $fileOnly);
            } else {
                return $this->_rmDir($path);
            }
        } else {
            return $this->_rmFile($path);
        }
    }

    private function _rmDirRecursive($dir, $fileOnly = false)
    {
        if (substr($dir, (strlen($dir) - 1), 1) != '/') {
            return false;
        }

        $fileList = $this->_lsFiles($dir);
        foreach ($fileList as $file) {
            $file = $dir . $file['name'];
            $result = $this->rm($file);
            if (!$result) {
                return false;
            }
        }

        $dirList = $this->_lsDirs($dir);
        foreach ($dirList as $newDir) {
            if ($newDir['name'] == '.' || $newDir['name'] == '..') {
                continue;
            }

            $newDir = $dir . $newDir['name'] . '/';
            $result = $this->_rmDirRecursive($newDir, $fileOnly);
            if (!$result) {
                return false;
            }
        }
        if (!$fileOnly) {
            $result = $this->_rmDir($dir);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    private function _rmDir($dir)
    {
        if (substr($dir, (strlen($dir) - 1), 1) != '/') {
            return false;
        }

        $result = @ftp_rmdir($this->_ftp, $dir);
        if (!$result) {
            return false;
        }

        return true;
    }

    private function _rmFile($file)
    {
        if (substr($file, 0, 1) != '/') {
            $actualDir = @ftp_pwd($this->_ftp);
            if (substr($actualDir, (strlen($actualDir) - 2), 1) != '/') {
                $actualDir .= '/';
            }
            $file = $actualDir . $file;
        }

        $result = @ftp_delete($this->_ftp, $file);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function get($remoteFile, $localFile, $overwrite = false, $mode = null)
    {
        if (!isset($mode)) {
            $mode = $this->checkExt($remoteFile);
        }

        $remoteFile = $this->_constructPath($remoteFile);

        if (file_exists($localFile) && !$overwrite) {
            return false;
        }

        if (file_exists($localFile) && !is_writeable($localFile) && $overwrite) {
            return false;
        }

        if (function_exists('ftp_nb_get')) {
            $result = @ftp_nb_get($this->_ftp, $localFile, $remoteFile, $mode);
            while ($result == FTP_MOREDATA) {
                $this->_announce('nb_get');
                $result = @ftp_nb_continue($this->_ftp);
            }
        } else {
            $result = @ftp_get($this->_ftp, $localFile, $remoteFile, $mode);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    public function checkExt($filename)
    {
        if (($pos = strrpos($filename, '.')) === false) {
            return $this->_param['mode'];
        }

        $ext = substr($filename, $pos + 1);

        if (isset($this->_ext[$ext])) {
            return $this->_ext[$ext];
        }

        return $this->_param['mode'];
    }

    private function _announce($event)
    {
        foreach ($this->_listeners as $id => $listener) {
            $this->_listeners[$id]->notify($event);
        }
    }

    public function put($localFile, $remoteFile, $overwrite = false, $mode = null, $options = 0)
    {
        if ($options & ($this->_code['NET_FTP_BLOCKING'] | $this->_code['NET_FTP_NONBLOCKING']) === ($this->_code['NET_FTP_BLOCKING'] | $this->_code['NET_FTP_NONBLOCKING'])) {
            return false;
        }

        $usenb = ! ($options & ($this->_code['NET_FTP_BLOCKING'] == $this->_code['NET_FTP_BLOCKING']));

        if (!isset($mode)) {
            $mode = $this->checkExt($localFile);
        }

        $remoteFile = $this->_constructPath($remoteFile);

        if (!file_exists($localFile)) {
            return false;
        }
        if ((@ftp_size($this->_ftp, $remoteFile) != -1) && !$overwrite) {
            return false;
        }

        if (function_exists('ftp_alloc')) {
            ftp_alloc($this->_ftp, filesize($localFile));
        }

        if ($usenb && function_exists('ftp_nb_put')) {
            $result = @ftp_nb_put($this->_ftp, $remoteFile, $localFile, $mode);
            while ($result == FTP_MOREDATA) {
                $this->_announce('nb_put');
                $result = @ftp_nb_continue($this->_ftp);
            }
        } else {
            $result = @ftp_put($this->_ftp, $remoteFile, $localFile, $mode);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    public function getRecursive($remotePath, $localPath, $overwrite = false, $mode = null)
    {
        $remotePath = $this->_constructPath($remotePath);
        if ($this->_checkRemoteDir($remotePath) !== true) {
            return false;
        }

        if (!file_exists($localPath)) {
            $result = mkdir($localPath);
            if (!$result) {
                return false;
            }
        } else if (!is_dir($localPath)) {
            return false;
        }

        $dirList = $this->ls($remotePath, 'NET_FTP_DIRS_ONLY');

        if (!empty($dirList)) {
            foreach ($dirList as $dirEntry) {
                if ($dirEntry['name'] != '.' && $dirEntry['name'] != '..') {
                    $remotePathNew = $remotePath . $dirEntry['name'] . '/';
                    $localPathNew  = $localPath . $dirEntry['name'] . '/';
                    $result        = $this->getRecursive($remotePathNew, $localPathNew, $overwrite, $mode);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }

        $fileList = $this->ls($remotePath, 'NET_FTP_FILES_ONLY');
        if (!empty($fileList)) {
            foreach ($fileList as $fileEntry) {
                $remoteFile = $remotePath . $fileEntry['name'];
                $localFile  = $localPath . $fileEntry['name'];
                $result     = $this->get($remoteFile, $localFile, $overwrite, $mode);
                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    public function putRecursive($localPath, $remotePath, $overwrite = false, $mode = null)
    {
        $remotePath = $this->_constructPath($remotePath);
        if (!file_exists($localPath) || !is_dir($localPath)) {
            return false;
        }

        $oldPath = $this->pwd();
        if (!$this->cd($remotePath)) {
            $result = $this->mkdir($remotePath);
            if (!$result) {
                return false;
            }
        }

        $this->cd($oldPath);
        if ($this->_checkRemoteDir($remotePath) !== true) {
            return false;
        }

        $dirList = $this->_lsLocal($localPath);
        if (!empty($dirList['dirs'])) {
            foreach ($dirList['dirs'] as $dirEntry) {
                $remotePathNew = $remotePath . $dirEntry . '/';
                $localPathNew  = $localPath . $dirEntry . '/';
                $result        = $this->putRecursive($localPathNew, $remotePathNew, $overwrite, $mode);
                if (!$result) {
                    return false;
                }
            }
        }

        if (!empty($dirList['files'])) {
            foreach ($dirList['files'] as $fileEntry) {
                $remoteFile = $remotePath . $fileEntry;
                $localFile  = $localPath . $fileEntry;
                $result     = $this->put($localFile, $remoteFile, $overwrite, $mode);
                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    private function _lsLocal($dirPath)
    {
        $_dp      = opendir($dirPath);
        $dirList  = array();
        $fileList = array();

        while ($entry = readdir($_dp)) {
            if (($entry != '.') && ($entry != '..')) {
                if (is_dir($dirPath . $entry)) {
                    $dirList[] = $entry;
                } else {
                    $fileList[] = $entry;
                }
            }
        }

        closedir($_dp);

        $result['dirs']  = $dirList;
        $result['files'] = $fileList;

        return $result;
    }

    public function addExt($mode, $ext)
    {
        $this->_ext[$ext] = $mode;

        return $this;
    }

    public function removeExt($ext)
    {
        if (isset($this->_ext[$ext])) {
            unset($this->_ext[$ext]);
        }

        return $this;
    }

    public function getExtFile($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        if (!is_readable($filename)) {
            return false;
        }

        $exts = parse_ini_file($filename, true);
        if (!is_array($exts)) {
            return false;
        }

        $this->_ext = array();

        if (isset($exts['ASCII'])) {
            foreach ($exts['ASCII'] as $ext => $bogus) {
                $this->_ext[$ext] = FTP_ASCII;
            }
        }

        if (isset($exts['BINARY'])) {
            foreach ($exts['BINARY'] as $ext => $bogus) {
                $this->_ext[$ext] = FTP_BINARY;
            }
        }

        return true;
    }

    public function attach($observer)
    {
        if (!is_a($observer, 'System_Net_Ftp_Observer')) {
            return false;
        }

        $this->_listeners[$observer->getId()] = $observer;

        return true;
    }

    public function detach($observer)
    {
        if (!is_a($observer, 'System_Net_Ftp_Observer') || !isset($this->_listeners[$observer->getId()])) {
            return false;
        }

        unset($this->_listeners[$observer->getId()]);

        return true;
    }

    public function setDirMatcher($pattern, $matchmap)
    {
        if (!is_string($pattern)) {
            return false;
        }
        if (!is_array($matchmap)) {
            return false;
        } else {
            foreach ($matchmap as $val) {
                if (!is_numeric($val)) {
                    return false;
                }
            }
        }

        $this->_matcher = array('pattern' => $pattern, 'map' => $matchmap);
    }

    public static function natSort($item1, $item2)
    {
        return strnatcmp($item1['name'], $item2['name']);
    }

}
