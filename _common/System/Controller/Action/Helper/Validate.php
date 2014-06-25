<?php
class System_Controller_Action_Helper_Validate extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Zend_Filter の配列
     *
     * @var array
     */
    private $_filters = array();

    /**
     * Zend_Validator の配列
     *
     * @var array
     */
    private $_validators = array();

    /**
     * Zend_Filter_Input のオプション配列
     *
     * @var array
     */
    private $_options = array();

    public function __construct()
    {
        $module     = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();

        if (Zend_Registry::getInstance()->offsetExists('form')) {
            $config = Zend_Registry::getInstance()->get('form');

            if (isset($config->Zend_Form_Input)) {
                $this->_options = $config->Zend_Form_Input->options->toArray();
            }

            if (isset($config->{$module}->{$controller})) {
                $config = $config->{$module}->{$controller};

                if (isset($config->filters)) {
                    $this->_filters = $config->filters->toArray();
                }
                if (isset($config->validators)) {
                    $this->_validators = $config->validators->toArray();
                }
            }
        }
    }

    public function setAllowEmpty($key)
    {
        $this->_validators[$key]['allowEmpty'] = true;

        return $this;
    }

    public function setDenyEmpty($key)
    {
        $this->_validators[$key]['allowEmpty'] = false;

        return $this;
    }

    public function setFilters($filters)
    {
        $this->_filters = $filters;

        return $this;
    }

    public function setValidators($validators)
    {
        $this->_validators = $validators;

        return $this;
    }

    public function getFilters()
    {
        return $this->_filters;
    }

    public function getValidators()
    {
        return $this->_validators;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function isValid($data = null)
    {
        // バリデーターが無い場合
        if (empty($this->_validators)) {
            return true;
        }

        // データが与えられていない場合、リクエストデータをセットする
        if (is_null($data)) {
            $data = $this->getRequest()->getParams();
        }

        // Zend_Filter_Input をインスタンス化
        $input = new Zend_Filter_Input($this->_filters, $this->_validators, $data, $this->_options);

        // バリデート実行
        $result = $input->isValid();

        // エラーがある場合
        if ($result === false) {
            // エラーヘルパー取得
            $error = Zend_Controller_Action_HelperBroker::getStaticHelper('error');

            // エラーメッセージ取得
            $errors = $input->getInvalid();

            // エラーメッセージのキーを削除
            if (is_array($errors) && !empty($errors)) {
                foreach ($errors as $key => $value) {
                    $error->set($key, $value);
                }
            }
        }

        return $result;
    }

    public function direct($data = null)
    {
        return $this->isValid($data);
    }

}
