<?php
class System_View_Filter_Minify
{
    public function filter($value)
    {
        if (!strpos($value, '<textarea')) {
            $value = preg_replace(array('/>\s+/', '/\s+</', '/[\r\n]+/'), array('>', '<', ' '), $value);
        }

        return $value;
    }

}
