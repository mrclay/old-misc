<?php
ini_set('display_errors', 1);


$input = '
paragraph

paragraph    <div class="whatever"><blockquote>
    paragraph
  </blockquote>
  line
</div>

paragraph
<ul>
<li>line</li>
<li>paragraph

paragraph</li>
</ul>
paragraph
line
line
<pre>Honor
this whitespace
</pre>
paragraph
<style><!--
Do not alter!
--></style>
paragraph <!-- do not alter -->
<dl> <dt>term</dt> <dd>paragraph

<a href="xx"> <img src="yy" /> </a>

paragraph</dd> </dl>
<div><a href="xx"> <img src="yy" /> </a></div>

Hello <a href="link">

World</a>

<p id="abc">Paragraph</p>

<div>Line</div>';


$expected = '
<p>paragraph</p>

<p>paragraph    </p>
<div class="whatever"><blockquote>
<p>paragraph</p>
</blockquote>line</div>
<p>paragraph</p>
<ul><li>line</li><li>
<p>paragraph</p>

<p>paragraph</p>
</li></ul>
<p>paragraph<br />line<br />line</p>
<pre>Honor
this whitespace
</pre>
<p>paragraph</p>
<style><!--
Do not alter!
--></style>
<p>paragraph <!-- do not alter --></p>
<dl><dt>term</dt><dd>
<p>paragraph</p>

<p><a href="xx"> <img src="yy"></a></p>

<p>paragraph</p>
</dd></dl><div><a href="xx"> <img src="yy"></a></div>
<p>Hello <a href="link"><br /><br />World</a></p>
<p id="abc">Paragraph</p><div>Line</div>';

require dirname(__DIR__) . '/AutoP.php';

$autop = new MrClay_AutoP();
$output = $autop->process($input);

header('Content-Type: text/html;charset=utf-8');

if ($expected === $output) {
    echo "PASS!";
} else {
    echo "FAIL";
}

?><hr>
<pre><?php echo htmlspecialchars($output, ENT_QUOTES, 'UTF-8'); ?></pre>