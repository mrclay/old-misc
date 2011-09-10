<?php

namespace MrClay\Crypt;

use MrClay\Crypt\Hmac;

/**
 * Send/receive HMAC signed values over HTTP POST requests
 */
class SignedRequest {

    /**
     * @var \MrClay\Crypt\Hmac
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
     * @param string|\MrClay\Crypt\Hmac $password
     */
    public function __construct($password)
    {
        if ($password instanceof Hmac) {
            $this->hmac = $password;
        } else {
            $this->hmac = new Hmac($password);
        }
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
        return $this->hmac->sign(json_encode($value))->encode();
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
        $cont = Container::decode(new Encoding\Base64Url(), $str);
        if (! $cont || count($cont) !== 3) {
            $this->error = 'Invalid format';
            return array(false, null);
        }
        if (! $this->hmac->isValid($cont)) {
            $this->error = 'Hash invalid';
            return array(false, null);
        }
        $json = $cont[0]->getBytes();
        return array(true, $returnJson ? $json : json_decode($json, true));
    }
}
