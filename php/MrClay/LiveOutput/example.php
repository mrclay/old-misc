<?php

require '../LiveOutput.php';
require './Renderer.php';

$to = new MrClay_LiveOutput('MrClay_Html highlights');

require '../Html.php';
$html = new MrClay_Html;

$to->code("
\$html = new MrClay_Html;
");

$to->ob_start(); ?>

<p>Some inline HTML documentation.</p>

<?php
$to->code("
\$assoc = array(
    array('id' => 2, 'first' => '<b>B</b>íll', 'last' => 'Gátës'),
    array('id' => 3, 'first' => 'Batman', 'last' => null),
    array('id' => 4, 'first' => array(1, 'a', true), 'last' => false),
);
",
$assoc = array(
    array('id' => 2, 'first' => '<b>B</b>íll', 'last' => 'Gátës'),
    array('id' => 3, 'first' => 'Batman', 'last' => null),
    array('id' => 4, 'first' => array(1, 'a', true), 'last' => false),
)
);


$to->codeRender("
// non-string values are passed through \$html->stringify()
\$html->build_table_from_assoc_array(\$assoc);
",
$html->build_table_from_assoc_array($assoc)
);


function myStringify($var) {
    return $var ? '<i>truthy</i>' : '<b>falsey</b>';
}

$to->codeRender("
// custom stringify func
function myStringify(\$var) {
    return \$var ? '<i>truthy</i>' : '<b>falsey</b>';
}
\$html->build_table_from_assoc_array(\$assoc, array(
    'stringifyFunc' => 'myStringify'
));
",
$html->build_table_from_assoc_array($assoc, array(
    'stringifyFunc' => 'myStringify'
))
);



$to->codeRender("
\$html->build_table_from_assoc_array(\$assoc, array(
    'escapeValues' => false // don't escape cells (stringified cells are never escaped)
    ,'orientation' => 'horizontal'
));
",
$html->build_table_from_assoc_array($assoc, array(
    'escapeValues' => false
    ,'orientation' => 'horizontal'
))
);


$to->code("
// nicely formatted markup (slower)
\$html->set_whiteSpace(true);
",
$html->set_whiteSpace(true)
);


$to->codeReturn("
\$html->build_table_from_assoc_array(\$assoc, array(
    'escapeValues' => false
    ,'orientation' => 'horizontal'
));
",
$html->build_table_from_assoc_array($assoc, array(
    'escapeValues' => false
    ,'orientation' => 'horizontal'
))
);

$to->codeReturnRender("
\$html->build_dl_from_row(\$assoc[2]);
",
$html->build_dl_from_row($assoc[2])
);



$to->codeRender("
\$html->build_select(
	array( // attributes
		 'name' => 'mySelect'
		,'multiple' => 'multiple'
		,'size' => 3
	), array( // value => display
		 1 => 'Here is a select'
		,2 => 'element with'
		,3 => 'multiple selections.'
	), array( // selected values (optional)
		2,3
	)
);
",
$html->build_select(
	array( // attributes
		'name'=>'mySelect'
		,'multiple'=>'multiple'
		,'size'=>3
	), array( // value => display
		1 => 'Here is a select'
		,2 => 'element with'
		,3 => 'multiple selections.'
	), array( // selected values
		2,3
	)
)
);


$to->codeReturn("
// decode HTML entities
\$html->hd(\"HTML &euml;nt&iacute;ties c&aacute;n be &lt;em&gt;de&lt;/em&gt;coded.\");
",
$html->hd("HTML &euml;nt&iacute;ties c&aacute;n be &lt;em&gt;de&lt;/em&gt;coded.")
);


$to->codeReturnRender("
// escape and wrap in a P element
\$html->hwrap('HTML spëcial chars & áre <esc>aped.', 'p');
",
$html->hwrap('HTML spëcial chars & áre <esc>aped.','p')
);


$to->codeRender("
// wrap doesn't escape contents
\$html->wrap(
    \$html->hwrap('He said & she said.', 'p') // hwrap does
    ,'blockquote'
);
",
$html->wrap($html->hwrap('He said & she said.','p'),'blockquote')
);
