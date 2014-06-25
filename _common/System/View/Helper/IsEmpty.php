<?php
class System_View_Helper_IsEmpty extends Zend_View_Helper_Abstract
{
    public function isEmpty($value = null)
    {
        $isEmpty = true;

        if (!is_null($value)) {
            if (is_string($value)) {
                if ($value !== '') {
                    $isEmpty = false;
                }
            } else if (is_array($value)) {
                if (count($value) !== 0) {
                    $isEmpty = false;
                }
            } else {
                $isEmpty = false;
            }
        }

        return $isEmpty;
    }

}
