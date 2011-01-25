<?php

class MrClay_FireLog_Writer extends Zend_Log_Writer_Firebug {

    protected $_wildfireChannel;

    public function setWildfireChannel(Zend_Wildfire_Channel_HttpHeaders $channel)
    {
        $this->_wildfireChannel = $channel;
    }


    /**
     * Log a message to the Firebug Console.
     *
     * @param array $event The event data
     * @return void
     */
    protected function _write($event)
    {
        parent::_write($event);
        $this->_wildfireChannel->flush();
    }
}