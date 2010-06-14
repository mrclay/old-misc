<?php

class MrClay_LiveOutput_Renderer {
    public $title;
    protected $_headerSent = false;
    protected $_sectionOpened = false;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function header()
    {
        $this->_headerSent = true;
        header('Content-Type: text/html; charset=utf-8');
        ?><!doctype html><title>Live output: <?php echo htmlspecialchars($this->title); ?></title>
<style>
code {font-size:86%}
.code {padding:10px; background:#ffd;}
.section {padding:0 15px 15px; border:1px #fff solid; border-bottom:5px #000 solid; background:#eee}
.rendering {padding:10px; background:#fff;}
.produces {padding:10px; max-height:150px; overflow:auto;}
.html {padding:5px 10px; background: #fff;}
h2, h3 {margin:10px 0}
h1 small {color:#999;}
</style>
<body>
<h1><small>Live output:</small> <?php echo htmlspecialchars($this->title); ?></h1>
<?php
        flush();
    }

    protected function _highlight($php)
    {
        $h = highlight_string("<?php {$php} ?>", 1);
        $h = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;','<span style="color: #0000BB">', $h);
        $h = str_replace('<br />\'&nbsp;</span>', '\'</span>', $h);
        $h = str_replace('?&gt;', '', $h);
        $h = preg_replace('@<span [^>]*></span>@', '', $h);
        $h = preg_replace('@ #([A-F\\d])\\1([A-F\\d])\\2([A-F\\d])\\3"@', '#$1$2$3"', $h);
        return $h;
    }

    public function block($code, $return, $showReturn = false, $render = false)
    {
        if (! $this->_headerSent) {
            $this->header();
        }
        $code = $this->_highlight($code);
        if ($this->_sectionOpened) {
            echo "<div class=code>\n$code\n</div>";
            $this->_sectionOpened = false;
        } else {
            echo "<div class=section>\n<div class=code>\n$code\n</div>";
        }
        if ($showReturn) {
            $produces = $this->_highlight(var_export($return, 1));
            echo "\n<h3>returns</h3><div class=produces>\n$produces</div>";
        }
        if ($render && is_string($return)) {
            echo "\n<h3>rendering</h3>\n<div class=rendering>\n$return\n</div>\n";
        }
        echo "\n</div>\n";
        flush();
    }

    public function html($html = '')
    {
        echo "<div class=section><div class=html>$html</div>";
        flush();
        $this->_sectionOpened = true;
    }
}