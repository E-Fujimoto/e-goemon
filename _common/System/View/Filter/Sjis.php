<?php
class System_View_Filter_Sjis
{
    public function filter($value)
    {
        $value = preg_replace('/(<head>.*charset=)UTF-8(.*<\/head>)/siu', '\1Shift_JIS\2', $value);
        $value = mb_convert_encoding($value, 'SJIS-win');

        return $value;
    }

}
