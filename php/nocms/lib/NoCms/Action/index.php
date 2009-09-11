<?php

class NoCms_Action_index extends NoCms_Action {
    
    public function GET()
    {
        $matches = array();
        $d = dir($this->_nocms->getConfig('contentPath'));
        while (false !== ($entry = $d->read())) {
            if (preg_match('@^(.+?)(\.block\.html)$@', $entry, $m)) {
                $matches[] = $m;
            }
        }
        $d->close();
        
        $lis = array_map(array($this, '_matchesToItem'), $matches);
        
        header('Cache-Control: no-cache');
        $this->html('
            <h1>Available Content</h1>
            <ul>
            ' . implode('', $lis) . '
            </ul>
        ');
    }
    
    private function _matchesToItem($m)
    {
        list($file, $title, $ext) = $m;
        $formats = array(
            '.block.html' => 'HTML'
            ,'.inline.html' => 'HTML inline'
            ,'.txt' => 'text'
        );
        return "<li><a title='edit' href='" . 
            h($this->htmlRoot . '/index.php/edit/' . $file)
            . "'>" . h($title) . "</a> <small>(" 
            . h($formats[$ext]) . ")</small></li>";
    }
}
