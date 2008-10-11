var firstRun = 0;

// set default prefs
if (!pref('areSet')) {
	firstRun = 1;
	pref('user=');
	pref('count=50');
	pref('tags=');
	pref('minimizeAfterOpenUrl=1');
	pref('showFavicons=1');
	pref('showPlayers='); // not working :(
	pref('areSet=1');
}

function getDeliciousPosts(user, tags, count) {
	var 
		posts = [],
		qs = tags + '?count=' + count,
		url = 'http://del.icio.us/feeds/json/' + user + '/' + qs,
		script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = url;
	$('head', document).append(script);
	return Delicious.posts;
}

function openResults() {
	if (!pref('user')) {
		$('#widget').removeClass('hidePrefs').addClass('showPrefs');
		$('#user').get(0).focus();
		return;
	}
	$('#search').css({visibility:'hidden'});
	var 
		posts = getDeliciousPosts(pref('user'), pref('tags'), pref('count')),
		post, 
		i = 0, 
		markup = [],
		tag,
		tagLinks = [];
	markup.push('<ul class="', ulClass, '">');
	while (post = posts.shift()) {
		while (tag = post.t.shift()) {
			tagLinks.push('<a href="#">' + tag + '</a>');
		}
		markup.push('<li><a href="', post.u, '">');
		if (pref('showFavicons')) {
			markup.push(
				'<img src="', post.u.split('/').splice(0,3).join('/')+'/favicon.ico',
				'" onload="this.className=\'v\'" alt="">'
			);	
		}
		markup.push(post.d, '</a><small> in ', tagLinks.join(', '), '</small></li>');
		tagLinks = [];
	}
	var ulClass = pref('showFavicons')? '' : 'noIcons';
	markup.push('</ul>');
	$('#results').html(markup.join(''));
	if (pref('minimizeAfterOpenUrl')) {
		$('#results li > a:first-child').click(function(){
			widget.openURL(this.href);
			$('#widget').addClass('none');
			return false;
		});
	}
	$('#results small a').click(function(){
		pref('tags=' + this.innerHTML);
		$('#tags').get(0).value = this.innerHTML;
		openResults();
		return false;
	});
	if (pref('showPlayers')) Delicious.Mp3.go();
	$('#search').css({visibility:'visible'});
	$('#tags').get(0).focus();
}

var w;

// onload
$(document).ready(function(){
	w = $('#widget').get(0);
	// prefill prefs form
	$('#user').get(0).value = pref('user');
	$('#count').get(0).value = pref('count');
	$('#minimizeAfterOpenUrl').get(0).checked = pref('minimizeAfterOpenUrl');
	$('#showFavicons').get(0).checked = pref('showFavicons');
	//$('#showPlayers').get(0).checked = pref('showPlayers');
	$('#tags').get(0).value = pref('tags');
	
	if (firstRun) {
		$('#widget').removeClass('hidePrefs').addClass('showPrefs');
		$('#user').get(0).focus();
	} else {
		openResults();	
	}

	// search submit
	$('#search').get(0).onsubmit = function(){
		pref('tags=' + $('#tags').get(0).value);
		openResults();
		return false;
	};

	// set prefs submit
	$('#prefsForm').get(0).onsubmit = function() {
		pref('user=' + $('#user').get(0).value);
		pref('count=' + $('#count').get(0).value);
		var temp = $('#minimizeAfterOpenUrl').get(0).checked ? '1' : '';
		pref('minimizeAfterOpenUrl=' + temp);
		temp = $('#showFavicons').get(0).checked ? '1' : '';
		pref('showFavicons=' + temp);
		//temp = $('#showPlayers').get(0).checked ? '1' : '';
		//pref('showPlayers=' + temp);
		$('#widget').removeClass('showPrefs').addClass('hidePrefs');
		openResults();
		return false;
	};
	
	// toggle widget
	$('#del').click(function(){
		if (/\bnone\b/.test(w.className)) {
			$('#widget').removeClass('none');
			//openResults();
		} else {
			$('#widget').addClass('none');	
		}
	});
	
	// toggle prefs
	$('#flip').click(function(){
		if (/\bhidePrefs\b/.test(w.className)) {
			$('#widget').removeClass('hidePrefs').addClass('showPrefs');
		} else {
			$('#widget').removeClass('showPrefs').addClass('hidePrefs');
		}
	});
});