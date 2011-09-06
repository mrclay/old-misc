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
     * Get a valid value from a signed HTTP POST request
     *
     * @param array|null $postData
     *
     * @return array [isValid, valueReceived]
     */
    public function receive(array $postData = null)
    {
        if (! $postData) {
            $postData = $_POST;
        }
        if (! isset($postData['value'], $postData['salt'], $postData['hash'])) {
            $this->error = 'Missing at least one key: value, salt, hash';
            return array(false, null);
        }
        $isValid = $this->hmac->isValid(array($postData['value'], $postData['salt'], $postData['hash']));
        if ($isValid) {
            return array(true, unserialize($postData['value']));
        } else {
            $this->error = "Hash did not validate.";
            return array(false, null);
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
        $ctx = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($this->generatePost($value))
            )
         ));
        return file_get_contents($url, false, $ctx);
    }

    /**
     * @param mixed $value
     * 
     * @return array
     */
    public function generatePost($value)
    {
        list($data['value'], $data['salt'], $data['hash']) = $this->hmac->sign($value);
        $data['value'] = serialize($data['value']);
        return $data;
    }
}
