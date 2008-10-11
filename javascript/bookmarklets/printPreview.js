/* printPreview
 * http://mrclay.org/..
 * v1.0 alpha
 *
 * The script cycles through the media attributes of all stylesheets, and
 * adds "screen" to print SS and removes "screen" from screen-only SS.
 * So this action can be easily undone, a few non-existant media types are used.
 *
 * compression by: http://subsimple.com/bookmarklets/jsbuilder.htm
 */

(function() { // emulate print preview, this is reversible!
	var 
		i = 0, 
		m, 
		d = document, 
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
		for (i = ss.length - 1; 0 <= i; i--) { // 0 <= i for bookmarklet
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
})();