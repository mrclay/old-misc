/*! waitFor$ jQuery plugin by Steve Clay http://code.google.com/p/mrclay/source/browse/trunk */
/* 
 * If you include jquery and its plugins at the bottom of pages but must include some user
 * code using jQuery above it, you can create a global function called "waitFor$" and this
 * plugin will execute it. If you need multiple functions called, make "waitFor$" an array
 * of functions and they will be excuted in order.
 */
(function () {
	if (window._waitFor$) {
		if (window._waitFor$.length)
			for (var i = 0, l = window._waitFor$.length; i < l; ++i) {
				window._waitFor$[i]();
			}
		else 
			window._waitFor$();
		window._waitFor$ = null;
	}
})();