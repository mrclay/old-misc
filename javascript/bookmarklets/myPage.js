/* 
MyPage bookmarklet
Copyright 2006 Steve Clay
License: http://mrclay.org/licenses/bsd.html
*/
(function() {
	var
		d = document,
		i = 0,
		l,
		em = 0, // edit mode
		ed, // HTML editor
		b = d.body,
		w = window,
		sl = [], // selected
		hid = [],
		$ = b.getElementsByTagName('*'),
		cTp = (d.all && !w.opera)? 'absolute' : 'fixed', //ie
		css = d.createElement('style');
	css.type = 'text/css';
	css.media = 'all';
	// help panel
	var cT = '#mPp{font-size:15px;padding:5px;background:#fdd;color:#000;position:'+cTp+';top:0;right:0;zIndex:1000;text-align:right}#mPp:hover{padding:.5em;line-height:1.6;}#mPp:hover u{display:none}#mPp i{display:none;text-align:left;cursor:default;color:#000}#mPp:hover i{display:block}#mPp b{border:1px outset #000;background:#fff;color:#666;padding:0 2px;margin-right:4px}#mPe{position:absolute;left:0;right:0;padding:5px 10px;background:#fdd;text-align:left}#mPe textarea{width:99%;display:block}' + 
	// rest must be important
'.mPs,.mPs *{background:#ff0;color:#000;}.mPh{background:#ffc;}.mPi,.mPi *{background:#fff;width:auto;float:none;margin:1em 0;padding:0;}body.mPi{text-align:left;margin:0;}'.replace(/;/g,' !important;');
	if (css.styleSheet)
		css.styleSheet.cssText = cT;
	else
		css.appendChild(d.createTextNode(cT)); // webkit no like innerHTML
	d.getElementsByTagName('head')[0].appendChild(css);
	
	function nB(e) { // no event bubbling
		if (!e) var e = w.event; // IE
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	}
	function over(e) {
		nB(e);
		!em && !this.isSel && changeCn(this, 'mPh');
	}
	function out(e) {
		nB(e);
		!em && !this.isSel && changeCn(this);
	}
	function clik(e) {
		nB(e);
		!em && this.isSel? unSel(this) : sel(this);
		return false;
	}
	while (l = $.item(i++)) { // all elements
		addEvents(l);
	}
	var h = d.createElement('a'); //help panel
	h.id = 'mPp';
	h.href = 'http://mrclay.org/';
	h.innerHTML = '<u>?</u><i><b>R</b>emove Selected</i><i><b>U</b>ndo Remove</i><i><b>I</b>solate Selected</i><i><b>P</b>rint Preview</i><i><b>W</b>iden Selected</i><i><i><b>B</b>ackwards</i><b>N</b>ext</i><i><b>D</b>eselect</i><i><b>C</b>opy Element</i><i><b>E</b>dit HTML</i><i><b>Esc</b>ape/Quit</i>';
	h.onclick = function(e) {nB(e);};
	b.appendChild(h);
	d.onkeydown = function(e) {
		var ls = sl.length? sl[sl.length - 1] : 0; //last selection
		if (!e) var e = window.event;
		if (e.keyCode == 27) { // esc = exit, cleanup events
			rm(h);
			rm(css);
			i = 0;
			while (l = $.item(i++)) {
				l.onmouseover = l.oldOnmouseover || null;
				l.onmouseout = l.oldOnmouseout || null;
				l.onclick = l.oldOnclick || null;
				l.oldOnmouseover = null;
				l.oldOnmouseout = null;
				l.oldOnclick = null;
			}
			d.onkeydown = null;
			em && rm(ed);
		}
		if (em) return true;
		switch (e.keyCode) {
			case 82: // r = remove
				while (sl.length) {
					ls = sl[sl.length - 1];
					ls.style.display = 'none';
					unSel(ls);
					hid.push(ls);
				}
			break;
			case 73: // i = isolate
				sel(h);
				while (b.hasChildNodes()) b.removeChild(b.firstChild);
				while (sl.length) {
					b.appendChild(sl[0]);
					unSel(sl[0]);
				}
			break;
			case 87: // w = widen
				if (ls) {
					unSel(ls);
					(ls!=b) && sel(ls.parentNode);
				}
			break;
			case 80: // p = print preview
				pp();
			break;
			case 68: // d = deselect
				unSel(ls);
			break;
			case 85: // u = undo remove
				hid.length && (hid.pop().style.display = '');
			break;
			case 69: // e = edit HTML
				ls && ls.innerHTML && edit(ls);
			break;
			case 66: // b = before element
				if (ls) {
					l = d.getElementsByTagName('*').item(getSourceIndex(ls) - 1);
					if (l && l!=b) {
						unSel(ls);
						sel(l);
					}
				}
			break;
			case 78: // n = next element
				if (ls) {
					l = d.getElementsByTagName('*').item(getSourceIndex(ls) + 1);
					if (l && l!=h) {
						unSel(ls);
						sel(l);
					}
				}
			break;
			case 67: // c = copy
				if (ls) {
					l = ls.cloneNode(true);
					if (ls.id) {
						l.id += '_copy';
					}
					ls.parentNode.insertBefore(l, ls.nextSibling);
					addEvents(l);
					i = 0;
					var desc;
					while (desc = l.getElementsByTagName('*').item(i++))
						addEvents(desc);
					unSel(ls);
					sel(l);
				}
			break;
			default: return true;
		}
		return false;
	};
	function rm(l) {
		l.parentNode.removeChild(l);
	}
	function changeCn(l, cN) {
		l.className = l.className.replace(/\bmP[hs]\b/g, '');
		if (cN) l.className += l.className? ' ' + cN : cN;
	}
	function sel(l) {
		changeCn(l, 'mPs');
		l.isSel = '1';
		sl.push(l);
	}
	function unSel(l) {
		changeCn(l);
		l.isSel = '';
		for (var i=0, len=sl.length; i<len; i++) {
			if (sl[i]==l) {
				sl.splice(i, 1);
				return;
			}
		}
	}
	function edit(l) { // here we go
		em = 1;
		var 
			left = 0, // http://www.quirksmode.org/js/findpos.html :)
			top = 0,
			tmp = l,
			chg = 0; // has textarea changed
		if (tmp.offsetParent) {
			while (tmp.offsetParent) {
				left += tmp.offsetLeft;
				top += tmp.offsetTop;
				tmp = tmp.offsetParent;
			}
		}
		ed = d.createElement('div'); //editor
		ed.id = 'mPe';
		b.appendChild(ed);
		ed.style.top = (top + l.offsetHeight + 5) + 'px';
		unSel(l);
		var oh = getOuterHTML(l
			).replace(/^\s*|\s*$/g,'' 								// trim
			).replace(/ isSel[<>]*\u003E/g, '>'		// cust props (ie shows)
			).replace(/ class=""(?=[^<>]*>)/g, '');	// empty class
		sel(l);
		var rows = Math.min(15, oh.split('\n').length + 3);
		ed.innerHTML = '<textarea id=mPta rows='+rows+'></textarea><button id=mPbu>done</button>';
		d.getElementById('mPta').value = oh;
		d.getElementById('mPta').onchange = function() {
			chg = 1;
		};
		function finEdit() {
			unSel(l);
			em = 0;
			if (chg) {
				var tmp = b.appendChild(d.createElement('div'));
				tmp.innerHTML = d.getElementById('mPta').value;
				i = 0;
				var desc;
				while (desc = tmp.getElementsByTagName('*').item(i++))
					addEvents(desc);
				while (tmp.hasChildNodes()) 
					l.parentNode.insertBefore(tmp.firstChild, l);
				rm(l);
				rm(tmp);
			}
			rm(ed);			
		}
		d.getElementById('mPbu').onclick = d.getElementById('mPbu').onkeypress = finEdit; // wk needed kp
	}
	function addEvents(l) {
		if ('String' != typeof l.isSel) {
			l.isSel = '';
			// cache old events and use new
			l.oldOnmouseover = l.onmouseover;
			l.oldOnmouseout = l.onmouseout;
			l.oldOnclick = l.onclick;
			l.onmouseover = over;
			l.onmouseout = out;
			l.onclick = clik;
		}
	}
	function getSourceIndex(l) {
		if (l.sourceIndex) return l.sourceIndex;
		i = 0;
		var el;
		while (el = d.getElementsByTagName('*').item(i)) {
			if (el==l) return i;	
			++i;
		}
	}
	function getOuterHTML(l) { // for gecko
		if (l.outerHTML) return l.outerHTML;
		var dv = d.createElement('div');
		dv.appendChild(l.cloneNode(true));
		return dv.innerHTML;
	}
	function pp() { // from printPreview.js
		var 
			i = 0, 
			m,
			//d = document,
			ss = d.styleSheets,
			wk = /webkit/i.test(navigator.userAgent);
		if (ss && !wk) { // use w3c
			for (i = 0; i<ss.length; i++) {
				m = ss[i].media;
				if (m.mediaText) // gecko
					m.mediaText = media(m.mediaText);
				else // ie
					ss[i].media = media(m);
			}
		} else { // limited to link and style elements
			ss = [];
			var r, l;
			while (l = d.getElementsByTagName('link').item(i++)) { // collect stylesheet links...
				r = l.getAttribute('rel');
				if (r && /^style/i.test(r))
					ss.push(l);
			}
			i = 0;
			while (l = d.getElementsByTagName('style').item(i++))
				ss.push(l); // ...and style elements
			for (i = ss.length - 1; i >= 0; i--) {
				if (wk)
					handleWk(ss[i]);
				else
					ss[i].media = media(ss[i].media);
			}
		}
		function media(m) {
			// opera wont store unknown media types, must use valid ones
			return (m=='all')? m
				: (/projection/.test(m))? 'print'
				: (/speech/.test(m))? 'screen'
				: (/print/.test(m))? 'screen,print,projection'
				: 'speech'; 
		}
		function handleWk(l) {
			if (/print/.test(l.media)) {
				var n = l.cloneNode(true);
				n.media = 'screen';
				l.parentNode.appendChild(n);
			} else if (/screen/.test(l.media)) {
				l.disabled = true;
			}
		}
	}
})();