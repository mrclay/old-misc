/*! Easing Equations v2.0 | September 1, 2003
 * (c) 2003 Robert Penner, all rights reserved.
 * This work is subject to the terms in http://www.robertpenner.com/easing_terms_of_use.html.
 * 
 * Ported to Javascript by Steve Clay 4/8/06
 * Port license: http://mrclay.org/licenses/bsd.html
 */
var Easing = {};

Easing.Linear = {
	easeNone : function(t, b, c, d) { // time, begin, change, duration
		return c*t/d + b;
	}
};
Easing.Linear.easeIn = 
Easing.Linear.easeOut = 
Easing.Linear.easeInOut = Easing.Linear.easeNone;

Easing.Expo = {
	easeIn : function(t, b, c, d) {
		return (t==0)? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
	},
	easeOut : function(t, b, c, d) {
		return (t==d)? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
	},
	easeInOut : function(t, b, c, d) {
		if (t==0) return b;
		if (t==d) return b+c;
		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
	}
};

Easing.Sine = {
	easeIn : function(t, b, c, d) {
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},
	easeOut : function(t, b, c, d) {
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},
	easeInOut : function(t, b, c, d) {
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	}
};

Easing.Circ = {
	easeIn : function(t, b, c, d) {
		return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
	},
	easeOut : function(t, b, c, d) {
		return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
	},
	easeInOut : function(t, b, c, d) {
		if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
		return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
	}
};

Easing.Bounce = {
	easeOut : function(t, b, c, d) {
		if ((t/=d) < (1/2.75)) {
			return c*(7.5625*t*t) + b;
		} else if (t < (2/2.75)) {
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
		} else if (t < (2.5/2.75)) {
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
		}
	},
	easeIn : function(t, b, c, d) {
		return c - Easing.Bounce.easeOut(d-t, 0, c, d) + b;
	},
	easeInOut : function(t, b, c, d) {
		if (t < d/2) return Easing.Bounce.easeIn (t*2, 0, c, d) * .5 + b;
		else return Easing.Bounce.easeOut (t*2-d, 0, c, d) * .5 + c*.5 + b;
	}
};

Easing.Back = {
	easeIn : function(t, b, c, d) {
		var s = 1.70158;
		return c*(t/=d)*t*((s+1)*t - s) + b;
	},
	easeOut : function(t, b, c, d) {
		var s = 1.70158;
		if (t==0) return b; // prevent rounding error
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	},
	easeInOut : function(t, b, c, d) {
		var s = 1.70158;
		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
	}
};

Easing.Elastic = {
	easeIn : function(t, b, c, d) {
		if (t==0) return b;
		if ((t/=d)==1) return b+c;
		var p=d*.3, a=c, s=p/4;
		return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
	},
	easeOut : function(t, b, c, d) {
		if (t==0) return b;
		if ((t/=d)==1) return b+c;  
		var p=d*.3, a=c, s=p/4;
		return (a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b);
	},
	easeInOut : function(t, b, c, d) {
		if (t==0) return b;
		if ((t/=d/2)==2) return b+c;
		var p=d*(.3*1.5), a=c, s=p/4;
		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
	}
};

Easing.Quad = {
	easeIn : function(t, b, c, d) {
		return c*(t/=d)*t + b;
	},
	easeOut : function(t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOut : function(t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t + b;
		return -c/2 * ((--t)*(t-2) - 1) + b;
	}
};

Easing.Cubic = {
	easeIn : function(t, b, c, d) {
		return c*(t/=d)*t*t + b;
	},
	easeOut : function(t, b, c, d) {
		return c*((t=t/d-1)*t*t + 1) + b;
	},
	easeInOut : function(t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t + b;
		return c/2*((t-=2)*t*t + 2) + b;
	}
};

Easing.Quart = {
	easeIn : function(t, b, c, d) {
		return c*(t/=d)*t*t*t + b;
	},
	easeOut : function(t, b, c, d) {
		return -c * ((t=t/d-1)*t*t*t - 1) + b;
	},
	easeInOut : function(t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
		return -c/2 * ((t-=2)*t*t*t - 2) + b;
	}
};

Easing.Quint = {
	easeIn : function(t, b, c, d) {
		return c*(t/=d)*t*t*t*t + b;
	},
	easeOut : function(t, b, c, d) {
		return c*((t=t/d-1)*t*t*t*t + 1) + b;
	},
	easeInOut : function(t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
		return c/2*((t-=2)*t*t*t*t + 2) + b;
	}
};