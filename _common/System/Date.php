<?php
/**
 * 日付・時間クラス
 *
 * Zend_Dateを基本とし、使いやすく拡張したクラス
 *
 * @author  $Author: T_T $
 * @version $Id: Date.php 2 2010-03-02 15:39:55Z T_T $
 */
class System_Date
{
    /**
     * タイムゾーン
     *
     * @var string
     */
    const TIMEZONE = 'Asia/Tokyo';

    /**
     * ロケール
     *
     * @var string
     */
    const LOCALE = 'ja_JP';

    /**
     * @var Zend_Date
     */
    private $_date = null;

    /**
     * 日付の配列
     *
     * @var array
     */
    private $_dates = array();

    /**
     * コンストラクタ
     *
     * Zend_Dateを読み込んでオブジェクト化する
     *
     * @param  void
     */
    public function __construct($dates = array())
    {
        date_default_timezone_set(self::TIMEZONE);

        $this->set($dates);
    }
    /**
     * 正式な日付かどうか調べる
     *
     * @param  string  $date   日付
     * @param  string  $format 日付フォーマット
     * @return boolean 正式な日付かどうかの真偽値
     */
    public function isDate($date, $format = null)
    {
        return Zend_Date::isDate($date, $format, self::LOCALE);
    }

    /**
     * Zend_Dateを再作成する
     *
     * @param  void
     * @return object Dateオブジェクト
     */
    public function reset()
    {
        $this->_dates = array();
        $this->_date  = new Zend_Date(self::LOCALE);

        $dates = $this->getAll();

        $this->_dates['year']   = $dates['Y'];
        $this->_dates['month']  = $dates['m'];
        $this->_dates['day']    = $dates['d'];
        $this->_dates['hour']   = $dates['h'];
        $this->_dates['minute'] = $dates['i'];
        $this->_dates['second'] = $dates['s'];

        $this->_date  = null;

        return $this;
    }

    /**
     * 日付・時間をセットする
     *
     * @param  string|array $key 日付・時間の配列
     * @param  string|int   $val 日付・時間の値
     * @return object       Dateオブジェクト
     */
    public function set($key = null, $val = null)
    {
        if (is_array($key)) {
            $this->_set($key);
        } else if (!empty($key)) {
            $this->_set(array($key => $val));
        } else {
            $this->reset();
        }

        return $this;
    }

    /**
     * 日付・時間をセットする
     *
     * @param  array $date 日付・時間の配列
     * @return void
     */
    private function _set($date = array())
    {
        foreach ($date as $key => $val) {
            $minus = false;
            if ($val < 0) {
                $minus = true;
                $val  *= -1;
            }

            $val = preg_replace('/[^\d]/', '', $val);

            if ($minus) {
                $val *= -1;
            }

            switch ($key) {
                case 'timestamp':
                    $this->_dates['timestamp'] = $val;
                    break;

                case 'longdate':
                    if (strlen($val) >= 4) {
                        $this->set('Y', intval(substr($val, 0, 4)));
                    }
                    if (strlen($val) >= 6) {
                        $this->set('M', intval(substr($val, 4, 2)));
                    }
                    if (strlen($val) >= 8) {
                        $this->set('D', intval(substr($val, 6, 2)));
                    }
                    if (strlen($val) >= 10) {
                        $this->set('H', intval(substr($val, 8, 2)));
                    }
                    if (strlen($val) >= 12) {
                        $this->set('I', intval(substr($val, 10, 2)));
                    }
                    if (strlen($val) >= 14) {
                        $this->set('S', intval(substr($val, 12, 2)));
                    }
                    break;

                case 'datetime':
                    $this->set('longdate', $val);
                    break;

                case 'date':
                    $this->set('longdate', $val);
                    break;

                case 'time':
                    if (strlen($val) >= 2) {
                        $this->set('H', intval(substr($val, 0, 2)));
                    }
                    if (strlen($val) >= 4) {
                        $this->set('I', intval(substr($val, 2, 2)));
                    }
                    if (strlen($val) == 6) {
                        $this->set('S', intval(substr($val, 4, 2)));
                    }
                    break;

                case 'year':
                case 'Y':
                case 'y':
                    $this->_dates['year'] = intval($val);
                    break;

                case 'month':
                case 'mon':
                case 'M':
                case 'm':
                    $this->_dates['month'] = intval($val);
                    break;

                case 'day':
                case 'D':
                case 'd':
                    $this->_dates['day'] = intval($val);
                    break;

                case 'hour':
                case 'H':
                case 'h':
                    $this->_dates['hour'] = intval($val);
                    break;

                case 'minute':
                case 'min':
                case 'I':
                case 'i':
                    $this->_dates['minute'] = intval($val);
                    break;

                case 'second':
                case 'sec':
                case 'S':
                case 's':
                    $this->_dates['second'] = intval($val);
                    break;

                default:
                    break;
            }
        }
    }

    public function create()
    {
        if (!empty($this->_dates) && is_array($this->_dates)) {
            $this->_date = new Zend_Date($this->_dates, null, self::LOCALE);
        } else {
            $this->_date = new Zend_Date(self::LOCALE);
        }

        return $this;
    }

    /**
     * 日付・時間を取得する
     *
     * @param  string $key 取得する日付・時間のキー
     * @return string 日付・時間
     */
    public function get($key = '')
    {
        $this->create();

        $date = '';

        switch ($key) {
            case 'timestamp':
                $date = $this->_date->get(Zend_Date::TIMESTAMP, self::LOCALE);
                break;

            case 'longdate':
                $date = $this->getLongDate();
                break;

            case 'datetime':
                $date = $this->_date->get('yyyy-MM-dd HH:mm:ss', self::LOCALE);
                break;

            case 'date':
                $date = $this->_date->get(Zend_Date::DATES, self::LOCALE);
                break;

            case 'date_short':
                $date = $this->_date->get(Zend_Date::DATE_SHORT, self::LOCALE);
                break;

            case 'time':
                $date = $this->_date->get(Zend_Date::TIMES, self::LOCALE);
                break;

            case 'year':
            case 'Y':
                $date = $this->_date->get(Zend_Date::YEAR, self::LOCALE);
                break;

            case 'y':
                $date = $this->_date->get(Zend_Date::YEAR_SHORT, self::LOCALE);
                break;

            case 'month':
            case 'mon':
            case 'M':
                $date = $this->_date->get(Zend_Date::MONTH, self::LOCALE);
                break;

            case 'm':
                $date = $this->_date->get(Zend_Date::MONTH_SHORT, self::LOCALE);
                break;

            case 'day':
            case 'D':
                $date = $this->_date->get(Zend_Date::DAY, self::LOCALE);
                break;

            case 'd':
                $date = $this->_date->get(Zend_Date::DAY_SHORT, self::LOCALE);
                break;

            case 'hour':
            case 'H':
                $date = $this->_date->get(Zend_Date::HOUR, self::LOCALE);
                break;

            case 'h':
                $date = $this->_date->get(Zend_Date::HOUR_SHORT, self::LOCALE);
                break;

            case 'minute':
            case 'min':
            case 'I':
                $date = $this->_date->get(Zend_Date::MINUTE, self::LOCALE);
                break;

            case 'i':
                $date = $this->_date->get(Zend_Date::MINUTE_SHORT, self::LOCALE);
                break;

            case 'second':
            case 'sec':
            case 'S':
                $date = $this->_date->get(Zend_Date::SECOND, self::LOCALE);
                break;

            case 's':
                $date = $this->_date->get(Zend_Date::SECOND_SHORT, self::LOCALE);
                break;

            case 'weekday':
            case 'W':
                $date = $this->_date->get(Zend_Date::WEEKDAY_NAME, self::LOCALE);
                break;

            case 'week':
            case 'w':
                $date = $this->_date->get(Zend_Date::WEEKDAY_8601, self::LOCALE);
                break;

            case 'weeknum':
            case 'T':
                $date = $this->_date->get(Zend_Date::WEEK, self::LOCALE);
                break;

            case 't':
                $date = $this->_date->get(Zend_Date::MONTH_DAYS, self::LOCALE);
                break;

            default:
                break;
        }

        return $date;
    }

    /**
     * 日付・時間を取得する
     *
     * @param  void
     * @return array 必要となる日付・時間の配列
     */
    public function getAll()
    {
        $this->create();

        $dates = array(
            'Y' => $this->get('Y'),
            'y' => $this->get('y'),
            'M' => $this->get('M'),
            'm' => $this->get('m'),
            'D' => $this->get('D'),
            'd' => $this->get('d'),
            'H' => $this->get('H'),
            'h' => $this->get('h'),
            'I' => $this->get('I'),
            'i' => $this->get('i'),
            'S' => $this->get('S'),
            's' => $this->get('s'),
            'W' => $this->get('W'),
            'w' => $this->get('w'),
            'T' => $this->get('T'),
            't' => $this->get('t'),
        );

        return $dates;
    }

    /**
     * 日付を取得する
     *
     * @param  mixed  $delimiter デリミタ
     * @return string 年月日の日付
     */
    public function getDate($delimiter = '')
    {
        $dates = $this->getAll();

        return join($delimiter, array($dates['Y'], $dates['M'], $dates['D']));
    }

    /**
     * 日付を取得する
     *
     * @param  mixed  $delimiter デリミタ
     * @return string 年月日の日付
     */
    public function getDatetime($delimiter = '')
    {
        $dates = $this->getAll();
        $date  = join($delimiter, array($dates['Y'], $dates['M'], $dates['D']));
        $time  = $dates['H'] . ':' . $dates['I'] . ':' . $dates['S'];

        return $date . ' ' . $time;
    }

    /**
     * 時間を取得する
     *
     * @param  mixed  $delimiter デリミタ
     * @return string 時分秒の時間
     */
    public function getTime($delimiter = '')
    {
        $dates = $this->getAll();

        return join($delimiter, array($dates['H'], $dates['I'], $dates['S']));
    }

    /**
     * 日付・時間を取得する
     *
     * @param  mixed  $delimiter デリミタ
     * @return string 年月日時分秒の日付
     */
    public function getLongDate($delimiter = '')
    {
        $dates = $this->getAll();

        return join($delimiter, array($dates['Y'], $dates['M'], $dates['D'], $dates['H'], $dates['I'], $dates['S']));
    }

}
