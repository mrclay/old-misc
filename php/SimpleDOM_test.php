<?php
require_once 'SimpleDOM.php';

if (isset($_GET['xml'])) { // XML

	$d = new XMLDocument('foo');
	$root = $d->root;
	$root->appendChild($d->createElement('bar'));
	
	// img isn't minimized in XML
	$img = $root->appendChild($d->createElement('img'));
	$img->appendChild($d->createTextNode('Some Text!'));
	
	$d->setWhiteSpace(new WhiteSpace_XML('  '));

	echo "<pre>".htmlspecialchars($d->toString());
	exit();
}

// new HTML doc with title
$d = new HTMLDocument('SimpleDOM Demo');

$page = $d->body->appendChild($d->createElement('div'));
$page->attributes['id'] = 'page';

// add new H1 to body
$h1 = $page->appendChild($d->createElement('h1'));
// add textNode to H1
$h1->appendChild($d->createTextNode('SimpleDOM Demo'));
unset($h1); // object stays in DOM

// add new P to body
$p = $page->appendChild($d->createElement('p'));
$strong = $p->appendChild($d->createElement('strong'));
$strong->appendChild($d->createTextNode('Hello'));
$p->appendChild($d->createTextNode(' World! '));
// set an attribute
$p->attributes['style'] = 'color:green';

// IMG auto-minimized in HTML
$img = $p->appendChild($d->createElement('img'));
$img->attributes = array(
	'src' => 'http://www.w3.org/Icons/w3c_home'
	,'alt' => 'W3C'
);

$ul = $page->appendChild($d->createElement('ul'));

$li = $ul->appendChild($d->createElement('li'));
$a = $li->appendChild($d->createElement('a'));
$a->attributes['href'] = "SimpleDOM_test.php?xml=1";
$a->appendChild($d->createTextNode('Test XML'));

$form = $page->appendChild($d->createElement('form'));
$div = $form->appendChild($d->createElement('div'));
$label = $div->appendChild($d->createElement('label'));
$label->appendChild($d->createTextNode('Choose one: '));
$select = $label->appendChild($d->createElement('select'));

$option = $select->appendChild($d->createElement('option'));
$option->appendChild($d->createTextNode('Option 1'));
$option = $select->appendChild($d->createElement('option'));
$option->appendChild($d->createTextNode('Option 2'));

// use whitespace manager (optional)
$d->setWhiteSpace(new WhiteSpace_HTML());

$markup = htmlspecialchars($d->toString());

$pre = $page->appendChild($d->createElement('pre'));
$pre->appendChild($d->createTextNode($markup));

// output!
echo $d->toString();

?>