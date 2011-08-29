<?php

namespace MrClay;

/**
 * Allows resending the current request internally (sending the browser's cookies and
 * headers). E.g. Your framework won't allow you to capture the ceomplete rendered page 
 * before it's sent out, but you need this to generate a PDF version.
 * 
 * <code>
 * $result = $replay->sendRequest();
 * if ($result) {
 *     // do stuff with $result['content'], etc.
 * }
 * </code>
 */
class Replay {
    
    const HEADER_NAME_REPLAY = 'X-Replay-Request';
    
    /**
     * Headers found in current request, and to be sent in the replay
     * 
     * @var array
     */
    public $requestHeaders = array();
    
    /**
     * Was the request/will the replay be a POST?
     * 
     * @var bool
     */
    public $wasPost = false;
    
    /**
     * Was the current request a play request?
     * 
     * @var bool
     */
    public $wasReplay = null;
    
    /**
     * POST data received, and to be re-posted in the replay
     * 
     * @var array
     */
    public $postData = array();
    
    /**
     * Allow replayed requests to send Content-Encoding headers (you may get responses
     * with gzipped content)
     * 
     * @var bool
     */
    public $allowContentEncoding = false;
    
    /**
     * Allow replay to contain headers that allow conditional responses
     * 
     * @var bool
     */
    public $allowConditionalGet = false;
    
    /**
     * If true, serializeHeaders() will add the header "X-Replay-Request", and 
     * sendRequest() will not replay a request that contains that header.
     * 
     * @var bool
     */
    public $preventLoops = true;
    
    /**
     * Allow only headers in the whitelist to be sent with the replay request
     * 
     * @var bool
     */
    public $enforceHeaderWhitelist = true;
    
    /**
     * @var array
     */
    public $headerWhitelist = array(
        'Host', 
        'User-Agent', 
        'Accept', 
        'Accept-Encoding', 
        'Accept-Language', 
        'Accept-Charset',
        'Authorization',
        'Content-Type',
        'Cookie',
        'Origin',
        'Referer',
    );    
    
    /**
     * @var \MrClay\CurrentRequest
     */
    protected $currentRequest = null;

    /**
     * @param \MrClay\CurrentRequest $req 
     */
    public function __construct(CurrentRequest $req = null)
    {
        if ($req) {
            $this->currentRequest = $req;
        }
        $this->requestHeaders = $this->getRequestHeaders();
        $this->wasReplay = isset($this->requestHeaders[self::HEADER_NAME_REPLAY]);
        $this->wasPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
        if (! empty($_POST)) {
            $this->postData = $_POST;
        }
    }
    
    /**
     * Get all headers from the current request
     * 
     * @return array
     */
    public function getRequestHeaders() {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }
        $ret = array();
        foreach ($_SERVER as $key => $val) {
            if (0 === strpos($key, 'HTTP_')) {
                $words = explode('-', substr($key, 5));
                $words = array_map('ucfirst', $words);
                $ret[implode('-', $words)] = $val;
            }
        }
        return $ret;
    }
    
    /**
     * Get a string containing headers to be sent with a replay request
     * 
     * @return string
     */
    public function serializeHeaders()
    {
        $headers = $this->requestHeaders;
        unset($headers['Connection']);
        if ($this->enforceHeaderWhitelist) {
            foreach ($headers as $key => $val) {
                if (! in_array($key, $this->headerWhitelist)) {
                    unset($headers[$key]);
                }
            }
        }
        if (! $this->allowConditionalGet) {
            unset($headers['If-Modified-Since']);
            unset($headers['If-None-Match']);
        }
        if (! $this->allowContentEncoding) {
            unset($headers['Accept-Encoding']);
        }
        $headers[self::HEADER_NAME_REPLAY] = '1';
        $strs = array();
        foreach ($headers as $key => $val) {
            $strs[] = "$key: $val";
        }
        return implode("\r\n", $strs) . "\r\n";
    }
    
    /**
     * @return array
     */
    public function getHttpStreamContextOptions()
    {
        $httpOpts = array(
            'method' => ($this->wasPost ? 'POST' : 'GET'),
            'header' => $this->serializeHeaders(),
            'follow_location' => false
        );
        if ($this->wasPost) {
            $httpOpts['content'] = http_build_query($this->postData);
        }
        return $httpOpts;
    }
    
    /**
     * @param array $opts
     * @param array $params
     * @return resource
     */
    public function createStreamContext($opts = array(), $params = array())
    {
        if (empty($opts['http'])) {
            $opts['http'] = $this->getHttpStreamContextOptions();
        }
        return stream_context_create($opts, $params);
    }
    
    /**
     * Send a replay request, adding the header "X-Replay-Request". If the current request
     * is a replay, return false if preventLoops is true.
     * 
     * @param string $url
     * @param resource $streamContext 
     * @return false|array with keys: success (bool), content (string), metaData (array)
     * 
     * @throws \InvalidArgumentException
     */
    public function sendRequest($url = null, $streamContext = null)
    {
        if ($this->preventLoops && $this->wasReplay) {
            return false;
        }
        if (! $url) {
            if (! $this->currentRequest) {
                throw new \InvalidArgumentException('sendRequest requires $url if a CurrentRequest class was not provided');
            } else {
                $url = $this->currentRequest->getUrl();
            }
        }
        if (! $streamContext) {
            $streamContext = $this->createStreamContext();
        }
        $fp = fopen($url, 'r', false, $streamContext);
        $meta = stream_get_meta_data($fp);
        $content = stream_get_contents($fp);
        fclose($fp);
        return array(
            'success' => ($content !== false),
            'content' => $content,
            'metaData' => $meta,
        );
    }
    
    /**
     * Sniff various info from the metadata's wrapper_data
     * 
     * @param array $meta
     * @return array
     */
    public function analyzeMetaData($meta) {
        $ret = array();
        foreach ($meta['wrapper_data'] as $line) {
            if (preg_match('@^HTTP/(\d\.\d) (\d\d\d)\b@', $line, $m)) {
                $ret['httpVersion'] = $m[1];
                $ret['code'] = (int) $m[2];
            } elseif (preg_match('@^([\\w\-]+)\\s*:\\s*(.*)@', $line, $m)) {
                $val = trim($m[2]);
                $ret['headersRaw'][$m[1]] = $val;
                $nameParts = explode('-', $m[1]);
                $nameParts = array_map('strtoupper', $nameParts);
                $ret['headers'][implode('_', $nameParts)] = $val;
            }
        }
        $ret['length'] = isset($ret['headers']['CONTENT_LENGTH'])
            ? (int) $ret['headers']['CONTENT_LENGTH']
            : null;
        $ret['encoding'] = null;
        if (isset($ret['headers']['CONTENT_ENCODING']) && $ret['headers']['CONTENT_ENCODING'] !== 'identity') {
            $ret['encoding'] = preg_replace('@^x\\-@i', '', strtolower($ret['headers']['CONTENT_ENCODING']));
        }
        return $ret;
    }
}