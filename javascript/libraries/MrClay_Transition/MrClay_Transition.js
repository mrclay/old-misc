/*! (c) 2006 Steve Clay http://www.mrclay.org/ 
 * @license http://mrclay.org/licenses/bsd.html
 **/
var MrClay = window.MrClay || {};

MrClay.Transition = function (length, desiredFps) {
	length = length || 1000;
	desiredFps = desiredFps || 40;
	this.frameDelay = Math.round(1000 / desiredFps);
	this.lastFrame = Math.floor(length / this.frameDelay);
	this.funcs = [];
	this.vars = [];
};

MrClay.Transition.prototype = {
    /**
     * t.trackElementStyle(100, 150, null, 'slider', 'width', 'px');
     * 
     * t.trackElementStyle('#fff', '#c00', Easing.Expo.easeOut, 'slider', 'backgroundColor');
     */
    trackElementStyle : function(from, to, easing, el, prop, unit) {
    	if (typeof el == 'string')
    		el = document.getElementById(el);
    	var
    		t = this.createTracker(from, to, easing),
    		f;
    	if (typeof from == 'number') {
    		unit = unit || 'px';
    		f = function() {
    			el.style[prop] = t() + unit;
    		};
    	} else {
    		f = function() {
    			el.style[prop] = 'rgb('	+ t() + ')';
    		}
    	}
    	this.addFrameFunction(f);
    }
    // returns pointer function to return the current value in range
    ,createTracker : function(from, to, easing) {
    	easing = easing || this._defaultEasing;
    	if (typeof from == 'number') {
    		var 
    			i = this._addNumberVar(from, to, easing),
    			self = this,
    			f = function() {
    				return self.vars[i].current;
    			};
    		return f;
    	}
    	// color
    	if (typeof from != 'object') from = this.parseRgbArray(from);
    	if (typeof to != 'object') to = this.parseRgbArray(to);
    	// store a numeric for each component
    	var rgb = [];
    	for (var i=0; i<3; i++) {
    		rgb[i] = this._addNumberVar(from[i], to[i], easing);
    	}
    	this.vars.push({
    		type : 1,
    		'components' : rgb,
    		current : from
    	});
    	i = this.vars.length - 1;
    	var
    		self = this,
    		f = function() {
    			return self.vars[i].current;
    		};
    	return f;
    }
    ,addFrameFunction : function(func) {
    	this.funcs.push(func);
    }
    ,setEndFunction : function(func) {
        this.endFunc = func;
    }
    ,begin : function(direction) {
    	this.direction = direction || 1;
    	this.loadFrame(
    		(this.direction == 1)? 0 : this.lastFrame
    	);
    	var self = this;
    	this.interval = setInterval(
    		function() {self.advanceFrame();}, this.frameDelay
    	);
    }
    ,stop : function() {
    	clearInterval(this.interval);
    }
    // parse string to [r,g,b] (0-255)
    ,parseRgbArray : function(c) {
    	var
    		i,
    		m;
    	if (m = c.match(/rgb\s*\(\s*([\d%\.]+)\s*,\s*([\d%\.]+)\s*,\s*([\d%\.]+)\s*\)/)) { // rgb(a,b,c)
    		m.shift();
    		for (i=0; i<3; i++)
    			m[i] *= /%/.test(m[i])? 100.0 : 1.0;
    		return m;
    	}
    	if (m = c.match(/^#(.)(.)(.)$/)) { // convert #abc to #aabbcc
    		c = '#' + m[1] + m[1] + m[2] + m[2] + m[3] + m[3];
    	}
    	if (m = c.match(/^#(..)(..)(..)$/)) { // #abcdef
    		m.shift();
    		for (i=0; i<3; i++)
    			m[i] = parseInt(m[i], 16);
    		return m;
    	}
    	return false;
    }
    ,advanceFrame : function() {
    	this.currentFrame += this.direction;
    	var fin = (this.direction == 1)? (this.currentFrame == this.lastFrame) : (this.currentFrame == 0);
    	if (fin) this.stop();
    	this._updateFrame();
    	if (fin && this.endFunc) this.endFunc();
    }
    ,loadFrame : function(f) {
    	this.currentFrame = f;
    	this._updateFrame();
    }
    ,_updateFrame : function() {
    	// update vars
    	var rgb, comp;
    	for (var i = 0, l = this.vars.length; i<l; i++) {
    		switch(this.vars[i].type) {
    			case 0: // number
    				if (this.vars[i].change != 0) {
    					this.vars[i].current = this.vars[i].easing(
    						this.currentFrame,
    						this.vars[i].initial,
    						this.vars[i].change,
    						this.lastFrame
    					);
    				}
    			break;
    			case 1: // color
    				rgb = [];
    				for (var j = 0; j<3; j++) {
    					comp = this.vars[this.vars[i].components[j]].current;
    					rgb.push(Math.min(Math.max(Math.round(comp), 0), 255));
    				}
    				this.vars[i].current = rgb;
    			break;
    		}
    	}
    	// call functions
    	for (i=0, l=this.funcs.length; i<l; i++) 
    		this.funcs[i]();
    }
    ,_defaultEasing : function(t, b, c, d) {
    	return c*t/d + b; // linear
    }
    ,_addNumberVar : function(from, to, easing) {
    	this.vars.push({
    		type : 0,
    		initial : from,
    		change : (to - from),
    		current : from,
    		easing : easing
    	});
    	return this.vars.length - 1;
    }
};
