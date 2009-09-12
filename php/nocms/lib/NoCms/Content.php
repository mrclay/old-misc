<?php

/**
 * base class for content
 * TODO: handle unicode filenames on Windows. 
 */
class NoCms_Content {
    protected $_file = null;
    protected $_type = '';
    protected $_ext = '';
    protected $_title = '';
    
    const FILENAME_PATTERN = '@(\.txt|\.block\.html)$@';
    
    // inline HTML not implemented yet
    //const FILENAME_PATTERN = '@(\.txt|\.(?:block|inline)\.html)$@';
    
    protected function __construct($file)
    {
        $this->_file = $file;
        $filename = basename($file);
        $beforeExt = substr($filename, 0, - strlen($this->_ext));
        $title = preg_replace('@([a-z])_([a-z])@i', '$1 $2', $beforeExt);
        $this->_title = ucwords($title);
    }
    
    // if readable file w/ matching filename, return Content obj
    public static function fromFile($file)
    {
        if (! is_file($file) || ! is_readable($file)) {
            return null;
        }
        $filename = basename($file);
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
        
        return new $class($file);
    }
    
    public function getTitle() { return $this->_title; }
    public function getFile() { return $this->_file; }
    public function getType() { return $this->_type; }
    
    public function update($content, $numBackups)
    {
        $this->_saveBackup($numBackups);
        return file_put_contents($this->_file, $content);
    }
    
    public function fetch()
    {
        return file_get_contents($this->_file);
    }
    
    private function _saveBackup($numBackups)
    {
        if (! $numBackups) {
            return;
        }
        // store current rev
        copy($this->_file, $this->_file . $_SERVER['REQUEST_TIME']);
        $revPattern = '@^' . preg_quote(basename($this->_file), '@') . '\\d+$@';
        // count backups newest to eldest
        $revCount = 0;
        foreach (scandir(dirname($this->_file), 1) as $entry) {
            if (preg_match($revPattern, $entry)) {
                $revCount++;
                if ($revCount > $numBackups) {
                    unlink(dirname($this->_file) . DIRECTORY_SEPARATOR . $entry);
                }
            }
        }
    }
}
