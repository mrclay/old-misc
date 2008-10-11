/*! selectText jQuery plugin by Steve Clay http://code.google.com/p/mrclay/source/browse/trunk */
/* 
 * Simple plugin to select a range of text within a text box/textarea
 */
(function () {

    /**
     * Select the text within a "text box"
     *
     * This function contains no jQuery code, portable!
     *
     * @param HTMLElement
     *
     * @param int begin the index of the beginning of the selection. If null, the
     * entire field is selected (and the second parameter is ignored)
     *
     * @param int end the index of the end of the selection. If null, the selection
     * will extend to the last character.
     */
    function selectText (el, begin, end) {
        var len = el.value.length;
        end = end || len;
        if (begin == null)
            el.select();
        else
            if (el.setSelectionRange)
                el.setSelectionRange(begin, end);
            else
                if (el.createTextRange) {
                    var tr = el.createTextRange()
                        ,c = "character";
                    tr.moveStart(c, begin);
                    tr.moveEnd(c, end - len);
                    tr.select();
                }
                else
                    el.select();
        el.focus();
    }

    /**
     * Select the text within the first element (should be a "text box")
     *
     * @param int begin the index of the beginning of the selection. If null, the
     * entire field is selected. (and the second parameter is ignored)
     *
     * @param int end the index of the end of the selection. If null, the selection
     * will extend to the last character.
     *
     * @return jQuery
     */
    jQuery.fn.selectText = function (begin, end) {
        this.size() && selectText(this.get(0), begin, end);
        return this;
    };

})();