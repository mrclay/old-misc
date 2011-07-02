<?php

class MrClay_FireLog_Response extends Zend_Controller_Response_Http {

    public function setHeader($name, $value, $replace = false)
    {
        if (headers_sent()) {
            return $this;
        }
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;
        header("$name: $value");
        return $this;
    }
}