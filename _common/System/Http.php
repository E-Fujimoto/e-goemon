<?php
/**
 * HTTP通信クラス
 *
 * @author $Author: T_T $
 * @version $Id: Http.php 8 2011-05-21 12:20:25Z T_T $
 */
class System_Http
{
    /**
     * 送信先URL
     *
     * @var string
     */
    private $_url = '';

    /**
     * 送信メソッド
     *
     * @var string
     */
    private $_method = '';

    /**
     * Refer
     *
     * @var string
     */
    private $_refer = '';

    /**
     * データの文字エンコード
     *
     * @var string
     */
    private $_encode = '';

    /**
     * 送信時のデータ配列
     *
     * @var array
     */
    private $_sendData = array();

    /**
     * Cookieデータをリセットするかどうか
     *
     * @var boolean
     */
    private $_resetCookie = false;

    /**
     * UserAgentの値
     *
     * @var string
     */
    private $_userAgent = '';

    /**
     * Zend_Http_Clientオブジェクト
     *
     * @var Zend_Http_Client
     */
    private $_http = null;

    /**
     * Zend_Http_Clientの設定パラメーター
     *
     * @var array
     */
    private $_httpConfig = array();

    /**
     * Zend_Http_Clientのカスタムリクエストヘッダー
     *
     * @var array
     */
    private $_httpParam = array();

    /**
     * 送信したリクエストのヘッダー
     *
     * @var string
     */
    private $_requestHeader = '';

    /**
     * ファイル送信データ
     */
    private $_file = array();

    /**
     * 送信結果のヘッダー
     *
     * @var string
     */
    private $_resultHeader = '';

    /**
     * 送信結果の本文
     *
     * @var string
     */
    private $_resultBody = '';

    /**
     * Content-Type
     *
     * @var string
     */
    private $_encType = '';

    /**
     * Timeout
     *
     * @var int
     */
    private $_timeout = null;

    /**
     * Max Redirects
     *
     * @var int
     */
    private $_maxRedirects = 10;

    /**
     * コンストラクタ
     *
     * Zend_Http_Clientオブジェクトが存在すればパラメーターをリセットし、無ければオブジェクト化する
     */
    public function __construct()
    {
        if (is_null($this->_http) || !($this->_http instanceof Zend_Http_Client)) {
            $this->_http = new Zend_Http_Client();
        }
    }

    /**
     * 送信先URLのsetter
     *
     * @param  string $url 送信先URL
     * @return object Http
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }

    /**
     * 送信先URLのgetter
     *
     * @param  void
     * @return string 送信先URL
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * 送信メソッドのsetter
     *
     * @param  string $method 送信メソッド
     * @return object Http
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);

        if ($method === 'GET' || $method === 'POST') {
            $this->_method = $method;
        }

        return $this;
    }

    /**
     * 送信メソッドのgetter
     *
     * @param  void
     * @return string 送信メソッド
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Referのsetter
     *
     * @param  string $refer Refer
     * @return object Http
     */
    public function setRefer($refer)
    {
        $this->_refer = $refer;

        return $this;
    }

    /**
     * Referのgetter
     *
     * @param  void
     * @return string Refer
     */
    public function getRefer()
    {
        return $this->_refer;
    }

    /**
     * データの文字エンコードのsetter
     *
     * @param  string $encode データの文字エンコード
     * @return object Http
     */
    public function setEncode($encode)
    {
        $this->_encode = $encode;

        return $this;
    }

    /**
     * データの文字エンコードのgetter
     *
     * @param  void
     * @return string データの文字エンコード
     */
    public function getEncode()
    {
        return $this->_encode;
    }

    /**
     * Content-Type の setter
     *
     * @param  string $encType Content-Type
     * @return object Http
     */
    public function setEncType($encType)
    {
        $this->_encType = $encType;

        return $this;
    }

    /**
     * Content-Type の getter
     *
     * @param  void
     * @return string Content-Type
     */
    public function getEncType()
    {
        return $this->_encType;
    }

    /**
     * Timeout の setter
     *
     * @param int $timeout Timeout
     * @return object Http
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;

        return $this;
    }

    /*
     * Timeout の getter
     *
     * @param void
     * @return int Timeout
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /*
     * MaxRedirects の setter
     *
     * @param int $maxRedirects MaxRedirects
     * @return object Http
     */
    public function setMaxRedirects($maxRedirects)
    {
        $this->_maxRedirects = $maxRedirects;

        return $this;
    }

    /*
     * MaxRedirects の getter
     *
     * @param void
     * @return int MaxRedirects
     */
    public function getMaxRedirects()
    {
        return $this->_maxRedirects;
    }

    /**
     * 送信時のデータの配列のsetter
     *
     * @param  mixed|array $sendData 送信時のデータの配列の名前、もしくは送信時のデータの名前と値の連想配列
     * @param  mixed       $value    送信時のデータの値（オプション）
     * @return object      Http
     */
    public function setSendData($sendData, $value = null)
    {
        if (is_array($sendData)) {
            foreach ($sendData as $key => $value) {
                $this->_setSendData($key, $value);
            }
        } else {
            $this->_setSendData($sendData, $value);
        }

        return $this;
    }

    /**
     * 送信時のデータの配列のsetter
     *
     * @param  mixed $key   送信時のデータの名前
     * @param  mixed $value 送信時のデータの値
     * @return void
     */
    private function _setSendData($key, $value)
    {
        if (is_array($value)) {
            if (!empty($this->_encode)) {
                $key = mb_convert_encoding($key, $this->_encode, mb_detect_encoding($key));
            }

            foreach ($value as $v) {
                if (!empty($this->_encode)) {
                    $v = mb_convert_encoding($v, $this->_encode, mb_detect_encoding($v));
                }

                $this->_sendData[$key][] = $v;
            }
        } else {
            if (!empty($this->_encode)) {
                $key   = mb_convert_encoding($key, $this->_encode, mb_detect_encoding($key));
                $value = mb_convert_encoding($value, $this->_encode, mb_detect_encoding($value));
            }

            $this->_sendData[$key] = $value;
        }
    }

    /**
     * 送信時のデータの配列のgetter
     *
     * @param  mixed $key  送信時のデータの名前（オプション）
     * @return mixed|array $keyに対応する値、もしくは送信時のデータの配列
     */
    public function getSendData($key = '')
    {
        if (!empty($key)) {
            return isset($this->_sendData[$key]) ? $this->_sendData[$key] : '';
        } else {
            return $this->_sendData;
        }
    }

    public function setUserAgent($userAgent)
    {
        $this->_userAgent = $userAgent;

        return $this;
    }

    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    public function _setUserAgent()
    {
        if (empty($this->_userAgent)) {
            $data = array(
                'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
                'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Win64; x64; Trident/6.0)',
                'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
                'Mozilla/5.0 (Windows NT 6.1; Win64; x64; Trident/7.0; rv:11.0) like Gecko ',
            );

            $this->_userAgent = $data[mt_rand(1, count($data)) - 1];
        }

        return $this;
    }

    /**
     * 送信時にCookieを保存するかどうかのsetter
     *
     * @param  boolean $resetCookie 送信時にCookieを保存するかどうかの真偽値
     * @return object  Http
     */
    public function setResetCookie($resetCookie)
    {
        if ($resetCookie == true) {
            $this->_resetCookie = true;
        } else {
            $this->_resetCookie = false;
        }

        return $this;
    }

    /**
     * 送信時にCookieを保存するかどうかのgetter
     *
     * @param  void
     * @return boolean 送信時にCookieを保存するかどうかの真偽値
     */
    public function getResetCookie()
    {
        return $this->_resetCookie;
    }

    /**
     * Zend_Http_Clientの設定パラメーターを設定する
     *
     * @param  void
     * @return void
     */
    private function _setHttpConfig()
    {
        if (empty($this->_userAgent)) {
            $this->_setUserAgent();
        }

        $this->_httpConfig    = array(
            'maxredirects'    => $this->_maxRedirects,
            'strictredirects' => false,
            'timeout'         => 10,
            'httpversion'     => 1.1,
            'adapter'         => 'Zend_Http_Client_Adapter_Socket',
            'keepalive'       => true,
            'encodecookies'   => false,
            'useragent'       => $this->_userAgent,
        );
    }

    /**
     * Zend_Http_Clientのリクエストヘッダーを設定する
     *
     * @param  void
     * @return void
     */
    private function _setHttpParam()
    {
        if (empty($this->_userAgent)) {
            $this->_setUserAgent();
        }

        $this->_httpParam = array(
            'Accept'          => '*/*',
            'Accept-Language' => 'ja',
            'UA-CPU'          => 'x86',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection'      => 'keep-alive',
            'Referer'         => $this->_refer,
            'User-Agent'      => $this->_userAgent,
        );
    }

    /**
     *  ファイルをセットする
     *
     *  @param  string $file ファイル名
     *  @param  string $name input の name
     *  @param  string $data ファイル内のデータ (ファイル名のファイルが存在している場合、省略可)
     *  @param  string $mime 引数を与えない場合、mime_content_type 関数を使用して自動で設定される
     *  @return Object Http
     */
    public function setFile($file, $name = '', $data = null, $mime = null)
    {
        if (is_array($file)) {
            $this->_file[] = $file;
        } else {
            $this->_file[] = array('file' => $file,
                                   'name' => $name,
                                   'data' => $data,
                                   'mime' => $mime);
        }

        return $this;
    }

    /**
     * 送信したリクエストヘッダーのsetter
     *
     * @param  string $requestHeader 送信したリクエストヘッダー
     * @return void
     */
    private function _setRequestHeader($requestHeader)
    {
        $this->_requestHeader = $requestHeader;
    }

    /**
     * 送信したリクエストヘッダーのgetter
     *
     * @param  void
     * @return string 送信したリクエストヘッダー
     */
    public function getRequestHeader()
    {
        return $this->_requestHeader;
    }

    /**
     * 送信結果のヘッダーのsetter
     *
     * @param  string $resultHeader 送信結果のヘッダー
     * @return void
     */
    private function _setResultHeader($resultHeader)
    {
        $this->_resultHeader = $resultHeader;
    }

    /**
     * 送信結果のヘッダーのgetter
     *
     * @param  void
     * @return string 送信結果のヘッダー
     */
    public function getResultHeader()
    {
        return $this->_resultHeader;
    }

    /**
     * 送信結果のbodyのsetter
     *
     * @param  string $resultBody 送信結果のbody
     * @return void
     */
    private function _setResultBody($resultBody)
    {
        $this->_resultBody = $resultBody;
    }

    /**
     * 送信結果のbodyのgetter
     *
     * @param  void
     * @return string 送信結果のbody
     */
    public function getResultBody()
    {
        return $this->_resultBody;
    }

    public function reset()
    {
        $this->_http = new Zend_Http_Client();

        $this->_userAgent = '';

        return $this->resetParameter();
    }

    public function resetParameter()
    {
        $this->_url         = '';
        $this->_method      = '';
        $this->_refer       = '';
        $this->_encode      = '';
        $this->_encType     = '';
        $this->_file        = array();
        $this->_sendData    = array();
        $this->_resetCookie = false;
        $this->_timeout     = null;

        return $this;
    }

    /**
     * HTTPリクエストを送信する
     *
     * @param  void
     * @return boolean リクエストが成功したかどうかの真偽値
     */
    public function send()
    {
        if (empty($this->_url) || empty($this->_method)) {
            return false;
        }

        // 全パラメーターを消去する
        $this->_http->resetParameters();

        // Cookieをリセットする
        if ($this->_resetCookie === true) {
            $this->_http->setCookieJar();
        }

        // 接続パラメーターの作成
        $this->_setHttpConfig();

        // Timeout を設定
        if (!is_null($this->_timeout) && is_numeric($this->_timeout)) {
            $this->_httpConfig['timeout'] = $this->_timeout;
        }

        // 接続パラメーターをセットする
        $this->_http->setConfig($this->_httpConfig);

        // カスタムヘッダーの作成
        $this->_setHttpParam();

        // カスタムヘッダーをセットする
        $this->_http->setHeaders($this->_httpParam);

        // 送信先URLをセットする
        $this->_http->setUri($this->_url);

        // Content-Type をセットする
        if (!empty($this->_encType)) {
            $this->_http->setEncType($this->_encType);
        }

        // 送信データをセットする
        if ($this->_method === 'GET') {
            $this->_http->setParameterGet($this->_sendData);
        } else if ($this->_method === 'POST') {
            $this->_http->setParameterPost($this->_sendData);
        }

        // ファイルをセット
        if (!empty($this->_file)) {
            foreach ($this->_file as $val) {
                $this->_http->setFileUpload($val['file'], $val['name'], $val['data'], $val['mime']);
            }
        }

        // 送信する
        $response = $this->_http->request($this->_method);

        // 送信したリクエストヘッダーを取得する
        $this->_setRequestHeader($this->_http->getLastRequest());

        // 送信結果のヘッダーを取得する
        $this->_setResultHeader($response->getHeadersAsString());

        // 送信結果のbodyを取得する
        $body = $response->getBody();
        $body = mb_convert_encoding($body, mb_internal_encoding(), mb_detect_encoding($body));
        $this->_setResultBody($body);

        // 送信結果を返す
        return $response->isSuccessful();
    }

    public function getCookieJar()
    {
        return $this->_http->getCookieJar();
    }

}
