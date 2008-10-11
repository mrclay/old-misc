/*! by Steve Clay http://code.google.com/p/mrclay/source/browse/trunk */
/* 
 * Unobfuscates mailto: links to (hopefully) reduce spam.
 * Hrefs like "mailto:my_name_is_john.doe_and_the_domain_is_google.com" are converted
 * to "mailto:john.doe@google.com". In case the script fails, the mailto is still fairly
 * human-readable.
 *
 * If jQuery is available, the function runs on DOMReady, o/w at window.onload
 */
(function(){
	var reMailto = /^mailto:my_name_is_([^_]+)_and_the_domain_is_([^_]+)$/,
		fixHref = function () {
			var m, reRemoveTitleIf = /^my name is/;
			if (m = this.href.match(reMailto)) {
				this.href = 'mailto:' + m[1] + '@' + m[2];
				if (reRemoveTitleIf.test(this.title)) {
					this.title = '';
				}
			}
		}
	;
	if (window.jQuery)
		jQuery(function () {jQuery('a[href^=mailto:my_name_is]').each(fixHref);})
	else
		(function(){
			var oo = window.onload,
				searchDoc = function () {
					var i = 0, l;
					while (l = document.links[i++]) {
						if (m = l.href.match(reMailto)) {
							fixHref.apply(l);
						}
					}	
				}
			;
			window.onload = function() {
				oo && oo();
				searchDoc();
			};
		})();
})();