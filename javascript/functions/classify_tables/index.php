<?php
if (!isset($_POST['o'])) {
	$o = array(
		"colN" => 'on',
		"rowN" => 'on',
		"evenCol" => 'on',
		"evenRow" => 'on',
		"markSpans" => 'on',
		"COLclass" => 'on',
		"includeNested" => ''
	);
} else {
	$o =& $_POST['o'];
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<title>classify_tables.js : Javascript to assist styling table columns and rows</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="classify_tables.js"></script>
<script type="text/javascript"><!--
window.onload = function() {
	var myOptions = {
		<?php
$myOptions = array();
foreach ($o as $option => $value) {
	$myOptions[] = "'{$option}':1";
}
echo join(",\n\t\t",$myOptions);
?>

	};
	var time1 = new Date().getTime();
	classify_tables(myOptions);
	var time2 = new Date().getTime();
	document.getElementById('time').appendChild(document.createTextNode((time2-time1)+'ms'));
}
// --></script>
<style type="text/css">
td, th {padding:5px; border:none;}

.evenRow {background:#efefef;}

.Adelaide {background:#E0FFCC;}
.row6 {background:#D4F2BF;}

.domestic, thead .evenRow .domestic  {background:#FFFFCC;}
.evenRow .domestic {background:#F2F2BF;}
.Adelaide .domestic {background:#E0FFA3;}
.row6 .domestic {background:#D6F599;}

thead * {background:#fff;}

.cherries {color:#CC0000;}
.col5, .col8 {color:#8B1D01;}
thead .col8, thead .col5 {text-align:left;}
.col4, .col7 {text-align:right;}
.row2 .colSpan {text-align:center;}
.apricots {color:#FF6633; text-align:center;}
.row1 .apricots {color:#000;}
.saleType {text-align:right;}

.classified_table {
border-collapse:collapse;
border:none;
width:90%;
}
.classified_table td, .classified_table th {
border:none;
}
.classified_table div small {
background:yellow;
color:#000;
font-weight:normal;
}

#nested1 td {border:1px solid red;}

</style>
</head>
<body>
<h1><a href="classify_tables.js">classify_tables.js</a></h1>
<h2>Javascript to assist styling table columns and rows </h2>
<form name="config" action="" method="post">
	<label>
	<input name="o[colN]" type="checkbox" id="colN" <?php if (isset($o['colN'])){echo " checked";} ?>>
	colN (number column cells)</label>
	<br>
	<label>
	<input name="o[rowN]" type="checkbox" id="rowN" <?php if (isset($o['rowN'])){echo " checked";} ?>>
	rowN (number TR elements)</label>
	<br>
	<label>
	<input name="o[evenCol]" type="checkbox" id="evenCol" <?php if (isset($o['evenCol'])){echo " checked";} ?>>
evenCol (mark even columns)</label>
	<br>
	<label>
	<input name="o[evenRow]" type="checkbox" id="evenRow" <?php if (isset($o['evenRow'])){echo " checked";} ?>>
evenRow (mark even TR elements)</label>
	<br>
	<label>
	<input name="o[markSpans]" type="checkbox" id="markSpans" <?php if (isset($o['markSpans'])){echo " checked";} ?>>
	markSpans (mark instances of colspan and rowspan)</label>
	<br>
	<label>
	<input name="o[COLclass]" type="checkbox" id="COLclass" <?php if (isset($o['COLclass'])){echo " checked";} ?>>
COLclass (copy COL classes to column cells)</label>
	<br>
	<label>
	<input name="o[includeNested]" type="checkbox" id="includeNested" <?php if (isset($o['includeNested'])){echo " checked";} ?>>
Include nested table(s)</label>
	<br>
	<label>
	<input name="o[debug]" type="checkbox" id="debug" <?php if (isset($o['debug'])){echo " checked";} ?>>
debug (display classes)</label>
	<br>
	<div><input name="reclassify" type="submit" value="classify_tables with options above">
	</div>
</form>
<h3>Table adapted from <a href="http://www.usability.com.au/resources/tables.cfm#very">Accessible Data Tables</a></h3>
<table id="prices" border="1" summary="Wholesale and retail prices of imported and domestic cherries and apricots in Sydney and Melbourne. There are three levels of column headings.">
	<caption>Imported and domestic cherry and apricot prices in Perth and Adelaide</caption>
	<colgroup>
		<col class="cities">
		<col class="saleType">
		<col class="apricots">
		<col class="cherries" span="2">
		<col class="apricots domestic">
		<col class="cherries domestic" span="2">
	</colgroup>
	<thead>
		<tr>
			<td></td>
			<td></td>
			<th id="imp" colspan="3">Imported</th>
			<th id="dom" colspan="3">Domestic</th>
		</tr>
		<tr>
			<td>
			</td>
			<td>
			<?php if (isset($o['includeNested'])) { ?>
				<table id="nested1" summary="nested table check">
					<colgroup>
						<col class="dimples">
						<col class="birthmarks">
					</colgroup>
					<tr><td>1</td><td rowspan="2">2</td></tr><tr><td>3</td></tr>
				</table>
			<?php } ?>
			</td>
			<th headers="imp" id="imp-apr">Apricots</th>
			<th headers="imp" id="imp-che" colspan="2">Cherries</th>
			<th headers="dom" id="dom-apr">Apricots</th>
			<th headers="dom" id="dom-che" colspan="2">Cherries</th>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<th headers="imp imp-che" id="imp-che-agrade">A Grade</th>
			<th headers="imp imp-che" id="imp-che-bgrade">B Grade</th>
			<td></td>
			<th headers="dom dom-che" id="dom-che-agrade">A Grade</th>
			<th headers="dom dom-che" id="dom-che-bgrade">B Grade</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th rowspan="2" id="wholesale">Perth</th>
			<th headers="perth" id="perth-wholesale">Wholesale</th>
			<td headers="imp imp-apr perth perth-wholesale">$1.00</td>
			<td headers="imp imp-che imp-che-agrade perth perth-wholesale">$9.00</td>
			<td headers="imp imp-che imp-che-bgrade perth perth-wholesale">$6.00</td>
			<td headers="dom dom-apr perth perth-wholesale">$1.20</td>
			<td headers="dom dom-che dom-che-agrade perth perth-wholesale">$13.00</td>
			<td headers="dom dom-che dom-che-bgrade perth perth-wholesale">$9.00</td>
		</tr>
		<tr>
			<th headers="perth" id="perth-retail">Retail</th>
			<td headers="imp imp-apr perth perth-retail">$2.00</td>
			<td headers="imp imp-che imp-che-agrade perth perth-retail">$12.00</td>
			<td headers="imp imp-che imp-che-bgrade perth perth-retail">$8.00</td>
			<td headers="dom dom-apr perth perth-retail">$1.80</td>
			<td headers="dom dom-che dom-che-agrade perth perth-retail">$16.00</td>
			<td headers="dom dom-che dom-che-bgrade perth perth-retail">$12.50</td>
		</tr>
		<tr class="Adelaide">
			<th rowspan="2" id="adelaide">Adelaide</th>
			<th id="adelaide-wholesale">Wholesale</th>
			<td headers="imp imp-apr adelaide adelaide-wholesale">$1.20</td>
			<td headers="imp imp-che imp-che-agrade adelaide adelaide-wholesale">N/A</td>
			<td headers="imp imp-che imp-che-bgrade adelaide adelaide-wholesale">$7.00</td>
			<td headers="dom dom-apr adelaide adelaide-wholesale">$1.00</td>
			<td headers="dom dom-che dom-che-agrade adelaide adelaide-wholesale">$11.00</td>
			<td headers="dom dom-che dom-che-bgrade adelaide adelaide-wholesale">$6.00</td>
		</tr>
		<tr class="Adelaide">
			<th id="adelaide-retail">Retail</th>
			<td headers="imp imp-apr adelaide adelaide-retail">$1.60</td>
			<td headers="imp imp-che imp-che-agrade adelaide adelaide-retail">N/A</td>
			<td headers="imp imp-che imp-che-bgrade adelaide adelaide-retail">$11.00</td>
			<td headers="dom dom-apr adelaide adelaide-retail">$2.00</td>
			<td headers="dom dom-che dom-che-agrade adelaide adelaide-retail">$13.00</td>
			<td headers="dom dom-che dom-che-bgrade adelaide adelaide-retail">$10.00</td>
		</tr>
	</tbody>
</table>
<p>Process Time: <span id="time"></span></p>
<p>Tested: <strong>Win:</strong> IE5+,Opera7+,FF <strong>Mac:</strong> Safari1.3+,IE5*</p>
<p>IE5/mac seems to misrender nested tables (which must be temporarily removed
	from the document).</p>
</body>
</html>