<?php
class System_Controller_Action_Helper_Pager extends Zend_Controller_Action_Helper_Abstract
{
    public function get($pageData, $options)
    {
        // ページャーソースデータの整形
        if (empty($pageData)) {
            $pageData = array();
        }
        if (is_string($pageData)) {
            $pageData = intval($pageData);
        }

        $options = $this->_checkOptions($options);

        $paginator = Zend_Paginator::factory($pageData);
        $paginator->setDefaultScrollingStyle($options['scroll']);
        $paginator->setItemCountPerPage($options['per'])
                  ->setPageRange($options['range'])
                  ->setCurrentPageNumber($options['current']);

        if (isset($options['partial']) && !empty($options['partial'])) {
            Zend_View_Helper_PaginationControl::setDefaultViewPartial($options['partial']);

            return $paginator;
        } else {
            return $paginator->getPages();
        }
    }

    private function _checkOptions($options)
    {
        if (!isset($options['current']) || empty($options['current'])) {
            $options['current'] = 1;
        }
        if (!isset($options['per']) || empty($options['per'])) {
            $options['per'] = 10;
        }
        if (!isset($options['scroll']) || empty($options['scroll'])) {
            $options['scroll'] = 'Sliding';
        }
        if (!isset($options['range']) || empty($options['range'])) {
            $options['range'] = 5;
        }

        return $options;
    }

    public function direct($pageData, $options)
    {
        return $this->get($pageData, $options);
    }

}
