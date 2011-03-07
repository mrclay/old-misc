<?php
ini_set('display_errors', 1);

$content = isset($_POST['content']) ? $_POST['content'] : '';
$content = str_replace(array("\r\n", "\r"), "\n", $content);

if ($content !== '') {
    require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP/WordPress.php';
    require_once dirname(__FILE__) . '/../../../../php/MrClay/AutoP.php';
    require_once dirname(__FILE__) . '/../../../../php/MrClay/Bench.php';
    $old = new MrClay_AutoP_WordPress();
    $new = new MrClay_AutoP();

    $bench = new MrClay_Bench(.5);
    do {
        $outOld = $old->process($content);
    } while ($bench->shouldContinue());
    $statsOld = $bench->meanTime . " (n=" . $bench->iterationsRun . ')';

    $bench->reset();
    do {
        $outNew = $new->process($content);
    } while ($bench->shouldContinue());
    $statsNew = $bench->meanTime . " (n=" . $bench->iterationsRun . ')';
}

function pre($html) {
    $html = htmlspecialchars($html, ENT_COMPAT, 'UTF-8');
    echo str_replace("\n", "<b>↲</b>\n", $html);
}

header('Content-Type: text/html;charset=utf-8');
?>
<style>
pre { white-space: pre; white-space: pre-wrap; word-wrap: break-word; font-size:13px }
pre b { background: #999; color: #fff; font-size: 11px; font-weight: 300 }
textarea { width: 80% }
p, h1, h2, h3, h4 {margin:0; padding:0 0 .4em}
p {border:1px dotted #ccc;}
</style>
<form action="" method="post">
    <p>Input: <textarea name="content" rows="20" cols="80"><?php
    echo htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
    ?></textarea><input type="submit" value="Process"></p>
</form>
<?php if (isset($outOld)): ?>
<hr>
<table border="1" cellspacing="0" cellpadding="5">
    <tr>
        <th>Input</th>
        <th>Algo</th>
        <th>Output</th>
        <th>Rendered</th>
        <th>Mean Time (s)</th>
    </tr>
    <tr>
        <td rowspan="2"><pre><?php pre($content); ?></pre></td>
        <td>wpautop</td>
        <td><pre><?php pre($outOld); ?></pre></td>
        <td><?php echo $outOld; ?></td>
        <td><?php echo $statsOld; ?></td>
    </tr>
    <tr>
        <td>AutoP</td>
        <td><pre><?php pre($outNew); ?></pre></td>
        <td><?php echo $outNew; ?></td>
        <td><?php echo $statsNew ?></td>
    </tr>
</table>
<?php endif; ?>