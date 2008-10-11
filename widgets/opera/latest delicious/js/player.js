// modified del.icio.us player
// have to use their server's player to avoid warning :(
if(typeof(Delicious) == 'undefined') Delicious = {}
Delicious.Mp3 = {
	playimg: null,
	player: null,
	go: function() {
		var all = document.getElementsByTagName('a')
		for (var i = 0, o; o = all[i]; i++) {
			if(o.href.match(/\.mp3$/i)) {
				var img = document.createElement('img')
				img.src = 'img/play.gif'; img.title = 'listen'
				img.height = img.width = 12
				img.style.border = 'none'
				img.style.marginLeft = '5px'
				img.style.cursor = 'pointer'
				img.onclick = Delicious.Mp3.makeToggle(img, o.href)
				o.parentNode.insertBefore(img, o.nextSibling)
	}}},
	toggle: function(img, url) {
		if (Delicious.Mp3.playimg == img) Delicious.Mp3.destroy()
		else {
			if (Delicious.Mp3.playimg) Delicious.Mp3.destroy()
			var a = img.nextSibling;
			img.src = 'img/stop.gif'; Delicious.Mp3.playimg = img;
			Delicious.Mp3.player = document.createElement('span')
			Delicious.Mp3.player.innerHTML = '<embed style="vertical-align:bottom;" src="http://del.icio.us/static/swf/mp3.swf" flashVars="theLink='+url+'&amp;fontColor=000000"'+
			'quality="high" width="60" height="14" name="player"' +
			'align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"' +
			' pluginspage="http://www.macromedia.com/go/getflashplayer" />'
			img.parentNode.insertBefore(Delicious.Mp3.player, img.nextSibling)
	}},
	destroy: function() {
		Delicious.Mp3.playimg.src = 'img/play.gif'; Delicious.Mp3.playimg = null
		Delicious.Mp3.player.removeChild(Delicious.Mp3.player.firstChild); Delicious.Mp3.player.parentNode.removeChild(Delicious.Mp3.player); Delicious.Mp3.player = null
	},
	makeToggle: function(img, url) { return function(){ Delicious.Mp3.toggle(img, url) }}
};