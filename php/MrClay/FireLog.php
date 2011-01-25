<?php

/**
 * This class allows you to use Zend_Wildfire_Plugin_FirePhp more like the old
 * FirePHP class. I.e. without managing request/response objects and flushing the
 * Wildfire channel.
 *
 * FireLog does two things: 1. It proxies Zend_Log method calls to an internal
 * Zend_Log. 2. It proxies the send, group, and groupEnd methods of
 * Zend_Wildfire_Plugin_FirePhp. In both cases the headers are set immediately.
 *
 * <code>
 * $log = MrClay_FireLog::getInstance();
 *
 * // Zend_Log methods
 * $log->info('An informational message.');
 * $log->warn('A warning!');
 * 
 * // Zend_Wildfire_Plugin_FirePhp methods
 * $log->group('My Group');
 * $log->send('Hello from the group.');
 * $log->groupEnd();
 * </code>
 */
class MrClay_FireLog {

    /**
     * @var Zend_Wildfire_Channel_HttpHeaders
     */
    protected $_channel;

    /**
     * @var Zend_Log
     */
    protected $_log;

    protected function __construct()
    {
        $this->_channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $this->_channel->setResponse(new MrClay_FireLog_Response());
        $this->_channel->setRequest(new Zend_Controller_Request_Http());
        $writer = new MrClay_FireLog_Writer();
        $writer->setWildfireChannel($this->_channel);
        $this->_log = new Zend_Log($writer);
    }

    /**
     * @return MrClay_FireLog
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Proxy calls back to Zend_Log
     */
    public function __call($name, $arguments) {
        call_user_func_array(array($this->_log, $name), $arguments);
    }

    /**
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }

    /**
     * Logs variables to the Firebug Console
     * via HTTP response headers and the FirePHP Firefox Extension.
     *
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @param  array  $options OPTIONAL Options to change how messages are processed and sent
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     * @throws Zend_Wildfire_Exception
     */
    public function send($var, $label=null, $style=null, $options=array())
    {
        $r = Zend_Wildfire_Plugin_FirePhp::send($var, $label, $style, $options);
        $this->_channel->flush();
        return $r;
    }

    /**
     * Starts a group in the Firebug Console
     *
     * @param string $title The title of the group
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public function group($title)
    {
        $r = Zend_Wildfire_Plugin_FirePhp::group($title);
        $this->_channel->flush();
        return $r;
    }

    /**
     * Ends a group in the Firebug Console
     *
     * @return TRUE if the group instruction was added to the response headers or buffered.
     */
    public function groupEnd()
    {
        $r = Zend_Wildfire_Plugin_FirePhp::groupEnd();
        $this->_channel->flush();
        return $r;
    }
}
