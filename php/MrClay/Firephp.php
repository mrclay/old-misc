<?php

/**
 * This class allows you to use Zend_Wildfire_Plugin_FirePhp more like the old
 * FirePHP class. I.e. without managing request/response objects and flushing the
 * Wildfire channel.
 *
 * When you call send()/group()/groupEnd(), the arguments are passed to the same static
 * method on Zend_Wildfire_Plugin_FirePhp, then the channel is flushed to an extended
 * version of Zend_Controller_Response_Http that calls header() immediately.
 * 
 * <code>
 * MrClay_Firephp::getInstance()->send($myVariable);
 * </code>
 */
class MrClay_Firephp {

    protected $_channel;

    protected function __construct()
    {
        $this->_channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $this->_channel->setResponse(new MrClay_Firephp_Response());
        $this->_channel->setRequest(new Zend_Controller_Request_Http());
    }

    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
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
    public static function groupEnd()
    {
        $r = Zend_Wildfire_Plugin_FirePhp::groupEnd();
        $this->_channel->flush();
        return $r;
    }
}