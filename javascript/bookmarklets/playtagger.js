(function () {
    if (window.Delicious && (Delicious.Mp3 || window.Mp3))
        return;
    var d = document
        ,s = d.createElement('script')
        ,wo = window.onload
        ,go = function () {
            Delicious.Mp3.go();
            window.onload = wo || null;
        }
    ;
    s.src = 'http://static.delicious.com/js/playtagger.js';
    if (null === s.onreadystatechange) 
        s.onreadystatechange = function () {
            if (s.readyState == 'complete')
                go();
        };
    else 
        s.onload = go;
    d.body.appendChild(s);
})();