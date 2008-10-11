/* 
Reload CSS Every bookmarklet
Copyright 2006 Stephen Clay
License: http://mrclay.org/licenses/bsd.html
*/
void(setInterval(
	function(){ 
		var qs = '?' + new Date().getTime(),
			l, ss, im,
			i = 0, j;
		while (l = document.getElementsByTagName('link')[i++]) {
			if (l.rel && 'stylesheet' == l.rel.toLowerCase()) {
				if (!l._h)
					l._h = l.href;
				l.href = l._h + qs;
			}
		}
	}
	, 2000 // 2s between CSS reloads
));

// ([^"' \/]+\.css)(?:\?\d+)?