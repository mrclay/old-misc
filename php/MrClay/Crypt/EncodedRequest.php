<?php

namespace MrClay\Crypt;

/**
 * Send/receive JSON-encoded values over HTTP POST requests
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class EncodedRequest {

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
     * Send a POST request containing the encoded value
     *
     * @param mixed $value
     *
     * @param $url
     *
     * @return string the returned response body
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
     * Get validate a value from an encoded HTTP request
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
        return json_encode(array(true, $value));
    }

    /**
     * Get valid JSON from an encoded string
     *
     * @param string $str
     *
     * @return array [isValid, value]
     */
    public function decode($str)
    {
        $result = json_decode($str, true);
        if (! is_array($result) || count($result) !== 2) {
            $this->error = 'Failed to decode';
            return array(false, null);
        }
        return array(true, $result[1]);
    }
}
