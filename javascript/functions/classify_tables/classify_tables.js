/*
Copyright 2005 (Nov 15) | Stephen Clay | http://mrclay.org/
See: http://mrclay.org/web_design/classify_tables/

This work is licensed under a Creative Commons Attribution 2.5 License
http://creativecommons.org/licenses/by/2.5/

USAGES

window.onload = function() { // fully classify #tableId1 and #tableId2
	var myOptions = {
		'colN':1,
		'rowN':1,
		'evenCol':1,
		'evenRow':1,
		'markSpans':1,
		'COLclass':1,
		'ids':'tableId1,tableId2,'
	};
	classify_tables(myOptions);
}

window.onload = function() { // zebra all tables
	var myOptions = {
		'evenCol':1,
		'evenRow':1,
	};
	classify_tables(myOptions);
}

CLASSNAMES ADDED TO DOM:

TABLE : classified_table (once complete)
TR    : evenRow, row#
TD/TH : evenCol, col#, {COL class}, colSpan, rowSpan

*/
function classify_tables(o) {
	if (typeof document.getElementsByTagName=='undefined') return;
	if (!o.ids) {
		var t, ti = 0;
		while (t=document.getElementsByTagName("table").item(ti++)) {
			_classify_table(t,o);
		}
	} else {
		o.ids = o.ids.split(",");
		for (var i=0; i<o.ids.length; i++) {
			_classify_table(document.getElementById(o.ids[i]),o);
		}
	}
}

function _classify_table(el, o) {
	var column, c, ci, r, ri, col, coli, columnClass, realColumn, skipCol, temp;
	var nt, nti = 0, nestedTables = [], placeHolders = [], newClasses = [];
	// remove nested tables
	while (nt=el.getElementsByTagName('table').item(0)) { // store nested tables
		nestedTables[nti] = nt.cloneNode(true);
		placeHolders[nti] = document.createElement('span');
		placeHolders[nti].id = 'classifyTablePlaceholder'+nti;
		nt.parentNode.replaceChild(placeHolders[nti],nt);
		nti++;
	}
	// process COL elements
	if (o.COLclass) {
		coli = 0;
		realColumn = 0;
		columnClass = [];
		while (col=el.getElementsByTagName("col").item(coli++)) {
			if (!col.span) {col.span = 1;} // for Opera
			for (var i=0; i<col.span; i++) {
				columnClass[realColumn] = (col.className)? (col.className + ' ') : '';
				realColumn++;
			}
		}
	}
	// for each row
	ri = 0;
	skipCol = [];
	while (r=el.getElementsByTagName("tr").item(ri++)) {
		if (o.rowN) {r.className = "row"+(ri)+" "+r.className;}
		if (o.evenRow && ri%2==0) {r.className = "evenRow "+r.className;}
		column = 1;
		// "eat" any colspans before first cell
		while (typeof skipCol[column]!='undefined' && skipCol[column]) {
			skipCol[column]--;
			column++;
		}
		// all row children
		c=r.childNodes;
		for (var i=0; i<c.length;i++) {
			if (c[i].nodeType != 1) {continue;} // skip non-elements
			// "eat" inner column spans			
			while (typeof skipCol[column]!='undefined' && skipCol[column]) {
				skipCol[column]--;
				column++;
			}
			newClasses = []; // classes to add to cell
			if (o.COLclass && columnClass.length) {newClasses.push(columnClass[column-1]);}
			if (o.colN) {newClasses.push("col"+column);}
			if (o.evenCol && column%2==0) {newClasses.push("evenCol");}
			if (!c[i].colSpan) {c[i].colSpan = 1;} // for Safari
			if (!c[i].rowSpan) {c[i].rowSpan = 1;} // for Safari
			// "feed" skipCol array with spans
			if (c[i].rowSpan>1) {
				for (var j = 0; j<c[i].colSpan; j++) {
					if (typeof skipCol[column+j]=="undefined") {
						skipCol[column+j] = 0;
					}
					skipCol[column+j] += c[i].rowSpan-1;
				}
				if (o.markSpans) {newClasses.push("rowSpan");}
			}
			if (o.markSpans && c[i].colSpan>1) {newClasses.push("colSpan");}
			// add new classes to cell
			c[i].className = newClasses.join(' ')+' '+c[i].className;
			column += c[i].colSpan;
			if (o.debug) {
				temp = c[i].innerHTML;
				c[i].innerHTML = ''; // for IE/mac
				c[i].innerHTML = temp+"<div><small>"+c[i].className+"</small></div>";
			}
		}
	}
	el.className = "classified_table "+el.className;
	// restore nested tables
	var ph;
	while (nti>0) {
		nti--;
		ph=document.getElementById('classifyTablePlaceholder'+nti);
		ph.parentNode.replaceChild(nestedTables[nti],ph);
	}
}

// Array Extensions  v1.0.6
// documentation: http://www.dithered.com/javascript/array/index.html
// license: http://creativecommons.org/licenses/by/1.0/
// code by Chris Nott (chris[at]dithered[dot]com)

if (typeof Array.prototype.push=="undefined") {
  Array.prototype.push = function() {
     var currentLength = this.length;
     for (var i = 0; i < arguments.length; i++) {
        this[currentLength + i] = arguments[i];
     }
     return this.length;
  };
}
