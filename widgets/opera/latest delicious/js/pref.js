if (typeof widget != 'undefined') { // in a widget

	var pref = function(str) {
		var m;
		if (m = str.match(/^([^=]+)=([\s\S]*)$/i)) { // set
			widget.setPreferenceForKey(m[2], m[1]);
			return m[2];
		} else {
			return widget.preferenceForKey(str);
		}
	};

} else { // testing in a page

	var widget = {};
	widget.openURL = function(url){
		window.open(url);
	};

	var testingPrefs = {};
	var pref = function(str) {
		var m;
		if (m = str.match(/^([^=]+)=([\s\S]*)$/i)) { // set
			testingPrefs[m[1]] = m[2];
			return m[2];
		} else {
			return (typeof testingPrefs[str] != 'undefined')
				? testingPrefs[str]
				: '';
		}
	};
	
}