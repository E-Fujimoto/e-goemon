<?php
class System_Controller_Action_Helper_Resource extends Zend_Controller_Action_Helper_Abstract
{
    private $_resources = array();

    public function get($resourceName)
    {
        // リソース名からクラス名を取得する
        $resourceName = strtolower($resourceName);
        $directories  = explode('_', $resourceName);
        for ($i = 0; $i < count($directories); $i++) {
            $temp = explode('-', $directories[$i]);

            for ($j = 0; $j < count($temp); $j++) {
                $temp[$j] = ucfirst($temp[$j]);
            }

            $directories[$i] = join('', $temp);
        }
        $className = join('_', $directories);

        // リソースがインスタンス化されていなければインスタンス化する
        if (!array_key_exists($resourceName, $this->_resources) ||
            !($this->_resources[$resourceName] instanceof $className)) {

            $this->_resources[$resourceName] = new $className();
        }

        return $this->_resources[$resourceName];
    }

    public function direct($resourceName)
    {
        return $this->get($resourceName);
    }

}
