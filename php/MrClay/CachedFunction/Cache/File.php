<?php

class MrClay_CachedFunction_Cache_File {
    protected $_base = null;
    
    public function __construct($dir = '/tmp', $basename = 'queuedFunc_')
    {
        $this->_base = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $basename;
    }
    
    public function get($id)
    {
        return (file_exists($this->_base . $id)
                && ($content = @file_get_contents($this->_base . $id)))
            ? unserialize($content)
            : false;
    }
    
    public function set($id, $value)
    {
        @file_put_contents($this->_base . $id, serialize($value));
    }    
    
    public function delete($id)
    {
        @unlink($this->_base . $id);
    }

    public function reset()
    {
        clearstatcache();
    }
}
