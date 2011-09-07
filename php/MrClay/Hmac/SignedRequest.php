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
                'content' => http_build_query($data)
            )
        ));
        return file_get_contents($url, false, $ctx);
    }

    /**
     * Get validate JSON from a signed HTTP request
     *
     * @param array $requestData
     *
     * @return array [isValid, json]
     */
    public function receive($requestData = null)
    {
        if (! $requestData) {
            $requestData = $_REQUEST;
        }
        if (empty($requestData[$this->varName]) || ! is_string($requestData[$this->varName])) {
            $this->error = "Value not present in request data or is not string";
            return array(false, null);
        }
        return $this->decode($requestData[$this->varName]);
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
     * @return array [isValid, json]
     */
    public function decode($str)
    {
        list($val, $salt, $hash) = explode('.', $str, 3);
        if (empty($salt) || empty($hash)) {
            $this->error = 'Invalid format';
            return array(false, null);
        }
        $json = MrClay_Hmac::base64urlDecode($val);
        if (false === $json) {
            $this->error = 'Base64urlDecode failed';
            return array(false, null);
        }
        if ($this->hmac->isValid(array($json, $salt, $hash))) {
            return array(true, $json);
        }
    }
}
