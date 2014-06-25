<?php
class System_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    protected $_view;

    public function init()
    {
        $view = $this->getView();

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        return $view;
    }

    public function getView()
    {
        if ($this->_view === null) {
            $options = $this->getOptions();
            $this->_view = new Revulo_View_Phtmlc($options);

            if (isset($options['doctype'])) {
                $this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
            }
            if (isset($options['debug'])) {
                $this->_view->debug = $options['debug'];
            } else {
                $this->_view->debug = false;
            }
        }

        return $this->_view;
    }

}
