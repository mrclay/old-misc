/* click2zap
 * 2008-10-10: Not sure if this version works!
 * v1.2 comments, no more innerHTML (embedable in HTML), tightening
 * v1.1 overhaul, release
 * v1.0 alpha
 */

// start compressed
//
// end compressed 

(function() {
	var pp = 0;
	if (pp)	{ // emulate print preview
	// the nested anonymous function call confused subsimple's compressor :(
		var i = 0, m, ss, d = document;
		if (ss = d.styleSheets) { // w3c
			for (i = 0; i<ss.length; i++) {
				m = ss[i].media;
				if (m.mediaText) { // gecko
					if (m.mediaText == 'screen')
						m.mediaText = 'rs';
					else if (m.mediaText.indexOf('print')!=-1) 
						m.mediaText += ', screen, as';
				} else { // ie
					if (m == 'screen')
						ss[i].media = 'rs';
					else if (m.indexOf('print')!=-1)
						ss[i].media += ', screen, as';
				}
			}
		} else { // limited to link and style elements
			ss = [];
			var r, l;
			while (l = d.getElementsByTagName('link').item(i++)) { // collect stylesheet links...
				r = l.getAttribute('rel');
				if (r && r.toLowerCase().indexOf('style')+1)
					ss.push(l);
			}
			i = 0;
			while (l = d.getElementsByTagName('style').item(i++))
				ss.push(l); // ...and style elements
			for (i = ss.length - 1; i >= 0; i--) {
				if (ss[i].media == 'screen')
					ss[i].media = 'rs';
				else if (ss[i].media.indexOf('print')!=-1)
					ss[i].media += ', screen, as';
			}
		}
	}	
	var i = 0, l, d = document, b = d.body, w = window;
	w.cZ = 1; // is active
	w.cZa = []; // collection of removed elements
	function nB(e) { // no event bubbling
		if (!e) var e = w.event; // IE
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	}
	while (l = b.getElementsByTagName('*').item(i++)) { // all elements
		l.onmouseover = function(e) {
			nB(e);
			if (w.cZ) this.style.background = 'yellow'; // if active, highlight
		};
		l.onmouseout = function(e) {
			nB(e);
			if (w.cZ) {
				this.style.background = ''; // the rest 4 safari:
				this.style.backgroundColor = '';
				this.style.backgroundImage = '';
				this.style.backgroundRepeat = '';
				this.style.backgroundPosition = '';
				this.style.backgroundAttachment = '';
			}
		};
		l.onclick = function(e) {
			nB(e);
			if (!w.cZ) return true; // bail if inactive, allow links
			this.style.background = ''; // unhighlight
			var h = d.createTextNode('');
			w.cZa.push(this, this.parentNode, h); // push ref to element, parent and empty text node
			this.parentNode.replaceChild(h,this); // replace el with empty text node
			return false;
		};
	}
	var c = d.createElement('div'); // "the toolbar"
	function wr(t) {c.appendChild(d.createTextNode(t));} // "write" to c
	function aU(newId, t) { // add U element to c
		var u = c.appendChild(d.createElement('u'));
		u.id = newId;
		u.appendChild(d.createTextNode(t));
	}
	wr('click2zap: '); aU('cZp', 'print'); wr(' | '); 
					   aU('cZt', 'disable'); wr(' | ');
					   aU('cZu', 'undo');
	// style toolbar
	var cs = c.style; 
	cs.padding = '.3em'; cs.background = 'red'; cs.color = '#fff';
	cs.position = 'fixed'; cs.top = '0'; cs.right = '0'; cs.zIndex = '9';
	b.insertBefore(c,b.firstChild); // toolbar first in BODY
	c.onclick = function(e) {nB(e);}; // no bubbling under toolbar
	d.getElementById('cZp').onclick = function(e) { // print and disable
		b.removeChild(c);
		w.cZ = 0;
		w.print();
	};
	d.getElementById('cZt').onclick = function(e) { // toggle active
		w.cZ = w.cZ? 0 : 1;
		this.innerHTML = w.cZ? 'disable' : 'enable';
	};
	d.getElementById('cZu').onclick = function(e) { // undo
		if (!w.cZa.length) return; // bail if no undos stored
		// pop off ref to element, parent and empty text node (in reverse order)
		var t = w.cZa.pop(), p = w.cZa.pop(), e = w.cZa.pop();
		p.replaceChild(e, t); // bring back element
	};
})();