<?php

class NoCms_Action_index extends NoCms_Action {
    
    public function GET()
    {
        $path = $this->_nocms->getConfig('contentPath');
        $blocks = array();
        foreach (scandir($path) as $entry) {
            $file = $path . DIRECTORY_SEPARATOR . $entry;
            
            // allow Content class to find objects
            $content = NoCms_Content::fromFile($file);
            if ($content) {
                $blocks[] = $content;
            }
        }
        
        $lis = array_map(array($this, '_matchesToItem'), $blocks);
        
        header('Cache-Control: no-cache');
        $this->html('
            <h1>Available Content</h1>
            <ul>
            ' . implode('', $lis) . '
            </ul>
        ');
    }
    
    private function _matchesToItem($block)
    {
        return "<li><a title='edit' href='" . 
            h($this->htmlRoot . '/index.php/edit/' . basename($block->getFile()))
            . "'>" . h($block->getTitle()) . "</a> <small>(" 
            . h($block->getType()) . ")</small></li>";
    }
}
