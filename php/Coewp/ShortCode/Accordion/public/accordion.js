jQuery(function ($) {
    $('div.page-accordion').each(function () {
        var that = this;
        var visible = 0;
        var expand = $(this).hasClass('page-accordion-e');
        // add show button and get H3.id in one step
        var h3Id = $('h3.page-accordion-heading', this).prepend('<span class="page-accordion-show">show</span>&nbsp;')[0].id;
        $(this)[0].className = 'page-accordion-js page-accordion-hidden';
        $('div.page-accordion-content', this)
        	.append('<div class="page-accordion-content-bottom"><span>hide</span></div>')
        	.children().first().each(function () {
        		// wpautop writes invalid markup, that creates an empty P in IE's DOM.
        		// we strip this P because it creates unwanted vertical space
        		if (this.nodeName == 'P' && !this.hasChildNodes()) {
            		$(this).remove();
            	}
        	});
        function toggle() {
        	visible = !visible;
            $('span.page-accordion-show', that).html(visible ? 'hide' : 'show');
            $(that).toggleClass('page-accordion-hidden page-accordion-visible');
            // must trigger re-layout for IE7 or IE8 in compat mode
            // @link http://stackoverflow.com/questions/1702399/how-can-i-force-reflow-after-dhtml-change-in-ie7/1702485#1702485
            document.body.className += '';
        }
        $('span.page-accordion-show, div.page-accordion-content-bottom span', this).click(toggle);
        // allow expand by settings or if page loaded to the heading's id
        if (expand || location.hash == ('#' + h3Id)) {
        	// not elegant to flash closed then reopen, but it'll do for now
        	toggle();
        }
    });
});
