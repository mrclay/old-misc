/*!
Private Page Validate bookmarklet
Copyright 2011 Stephen Clay
License: http://mrclay.org/licenses/bsd.html
http://www.mrclay.org/2011/09/13/validate-private-page-bookmarklet/
*/
(function (document) {
    var req = new XMLHttpRequest();
    req.onreadystatechange = function () {
        function addToF(name, value) {
            var i = document.createElement('input');
            i.name = name;
            i.value = value;
            i.type = 'hidden';
            f.appendChild(i);
        }
        if (req.readyState == 4) {
            var f = document.createElement('form');
            f.action = 'http://validator.w3.org/check';
            f.method = 'post';
            f.enctype = 'multipart/form-data';
            f.target = '_blank';
            addToF('fragment', req.responseText);
            addToF('prefill', '0');
            addToF('doctype', 'Inline');
            addToF('prefill_doctype', 'html401');
            addToF('group', '1');
            document.body.appendChild(f);
            f.submit();
            document.body.removeChild(f);
        }
    };
    req.open("GET", location.href, true);
    req.send("");
})(document);