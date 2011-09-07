<?php

/**
 * Send/receive HMAC signed values over HTTP POST requests
 */
class MrClay_Hmac_SignedRequest {

    /**
     * @var \MrClay_Hmac
     */
    protected $hmac;

    /**
     * @var string
     */
    public $error = '';

    /**
     * Name the encoded value is posted/received under
     *
     * @var string
     */
    public $varName = 'req';

    /**
     * @param string|MrClay_Hmac $secret
     */
    public function __construct($secret)
    {
        if ($secret instanceof MrClay_Hmac) {
            $hmac = $secret;
        } elseif (is_string($secret)) {
            $hmac = new MrClay_Hmac($secret);
        }
        $this->hmac = $hmac;
    }

    /**
     * @param mixed $value
     *
     * @param $url
     *
     * @return string 
     */
    public function send($value, $url)
    {
        $data[$this->varName] = $this->encode($value);
        $ctx = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data),
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            )
        ));
        return file_get_contents($url, false, $ctx);
    }

    /**
     * Get validate a value from a signed HTTP request
     *
     * @param array $requestData
     *
     * @param bool $returnJson return JSON instead of value in 2nd position
     *
     * @return array [isValid, value]
     */
    public function receive($requestData = null, $returnJson = false)
    {
        if (! $requestData) {
            $requestData = $_REQUEST;
        }
        if (empty($requestData[$this->varName]) || ! is_string($requestData[$this->varName])) {
            $this->error = "Value not present in request data or is not string";
            return array(false, null);
        }
        return $this->decode($requestData[$this->varName], $returnJson);
    }

    /**
     * Encode and sign 
     *
     * @param mixed $value
     *
     * @return string
     */
    public function encode($value)
    {
        $json = json_encode($value);
        list($data['value'], $data['salt'], $data['hash']) = $this->hmac->sign($json);
        return $this->hmac->base64urlEncode($json) . '.' . $data['salt'] . '.' . $data['hash'];
    }

    /**
     * Get valid JSON from a signed string
     *
     * @param string $str
     *
     * @param bool $returnJson return JSON instead of value in 2nd position
     *
     * @return array [isValid, value]
     */
    public function decode($str, $returnJson = false)
    {
        if (! preg_match('@^[^\\.]+\\.[^\\.]+\\.[^\\.]+$@', $str)) {
            $this->error = 'Invalid format';
            return array(false, null);
        }
        list($val, $salt, $hash) = explode('.', $str, 3);
        $json = MrClay_Hmac::base64urlDecode($val);
        if (false === $json) {
            $this->error = 'Base64urlDecode failed';
            return array(false, null);
        }
        if (! $this->hmac->isValid(array($json, $salt, $hash))) {
            $this->error = 'Hash invalid';
            return array(false, null);
        }
        return array(true, $returnJson ? $json : json_decode($json, true));
    }
}
