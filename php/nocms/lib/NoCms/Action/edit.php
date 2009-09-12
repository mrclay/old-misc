<?php

class NoCms_Action_edit extends NoCms_Action {
    
    public function GET($m)
    {
        if (! ($block = $this->_getBlock($m))) {
            $this->_nocms->redirect();
        }
        
        $scripts = '';
        if ($block->getType() !== 'Text') {
            $scripts = '
<script src="../../ckeditor/ckeditor.js"></script>
<script>
CKEDITOR.replace("blockContents");
</script>
            ';
        }
        
        // show editor
        header("cache-control: no-cache");
        $this->html('
            <h1>Edit: <em>' . h($block->getTitle()) . '</em></h1>
            <form action="" method="post">
            <p><textarea name="blockContents" rows="20" cols="80" style="width:95%">'
            . h($block->fetch())
            . '</textarea></p>
            <p>
                <input type="submit" value="Update"> or <strong><a href="../">cancel</a></strong>.
                <input type="hidden" name="sid" value="' . session_id() . '">
            </p>
            </form>
        ', $scripts);
    }
    
    public function POST($m)
    {
        if (session_id() !== $this->getPost('sid')
            || ! ($block = $this->_getBlock($m))) {
            $this->_nocms->redirect();
        }
        
        // syncronize newlines with saved file
        $fetched = $block->fetch();
        $posted = $this->getPost('blockContents');
        $posted = str_replace("\r\n", "\n", $posted);
        $blockNewline = $this->_getNewline($fetched);
        if ("\n" !== $blockNewline) {
            $posted = str_replace("\n", $blockNewline, $posted);
        }
        
        // update only if actually changed
        if (md5($posted) !== md5($fetched)) {
            $block->update($posted, $this->_nocms->getConfig('numBackups'));
        }
        $this->_nocms->redirect();
    }
    
    private function _getBlock($m)
    {
        // simple sniffing
        if (! isset($m[1])
            || '.' === $m[1][0]
            || false !== strpos($m[1], '/')
            || false !== strpos($m[1], '\\')) {
            return null;
        }
        // file/filename validation
        return NoCms_Content::fromFile(
            $this->_nocms->getConfig('contentPath') . DIRECTORY_SEPARATOR . $m[1]
        );
    }
    
    private function _getNewline($txt)
    {
        if ($txt !== str_replace("\r\n", "\n", $txt)) {
            return "\r\n";
        }
        if (false !== strpos($txt, "\r")) {
            return "\r";
        }
        return "\n";
    }
}
