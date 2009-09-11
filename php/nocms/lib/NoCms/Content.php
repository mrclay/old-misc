<?php

class NoCms_Content {
    protected $_file = null;
    protected $_type = '';
    protected $_ext = '';
    protected $_title = '';
    
    const FILENAME_PATTERN = '@(\.txt|\.(?:block|inline)\.html)$@';
    
    protected function __construct($filename, $path)
    {
        $base = substr($filename, 0, strlen($filename) - strlen($this->_ext));
        $title = preg_replace('@([a-z])_([a-z])@i', '$1 $2', $base);
        $this->_title = ucwords($title);
    }
    
    public static function fromFile($filename, $path)
    {
        if ('.' === $filename[0] 
            || ! preg_match(self::FILENAME_PATTERN, $filename, $m)) {
            return null;
        }
        $exts = array(
            '.txt' => 'Text'
            ,'.inline.html' => 'InlineHtml'
            ,'.block.html' => 'Html'
        );
        $class = 'NoCms_Content_' . $exts[$m[1]];
        
        return new $class($filename, $path);
    }
    
    public getTitle() { return $this->_title; }
    public getFile() { return $this->_file; }
    public getType() { return $this->_type; }
    
    public function update($content)
    {
        return file_put_contents($this->_file, $content);
    }
    
    public function fetch($content)
    {
        return file_get_contents($this->_file);
    }
    
    protected function _setTitle($filename)
    {
        $title = substr($filename, 0, strlen($filename) - strlen($this->_ext));
        $title = preg_replace('@([a-z])_([a-z])@i', '$1 $2', $title);
        $this->_title = ucwords($title);
    }
}
