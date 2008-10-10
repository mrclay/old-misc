var MrClay = window.MrClay || {};

MrClay.Graph_Default = {
	width : 120
	,height : 100
	,gridStroke : '#cccccc'
	,gridWidth : '.5'
	,axesWidth : '.5'
	,axesStroke : '#999999'
	,gridSpacingX : 20 // px
	,gridSpacingY : 20 // px
};

MrClay.Graph_Util = {
	_i : 0
	,createElements : function(parentId, width, height) {
		++this._i;
		var wrapper = document.getElementById(parentId).appendChild(
			document.createElement('div')
		);
		var canvas = wrapper.appendChild(document.createElement('canvas'));
		if (window.G_vmlCanvasManager) {
			canvas = G_vmlCanvasManager.initElement(canvas);
		}
		wrapper.id = 'MrClayGraphW' + this._i;
		wrapper.className = 'MrClayGraphW';
		canvas.id = 'MrClayGraphC' + this._i;

		// for Safari compat
		canvas.setAttribute('width', width);
		canvas.setAttribute('height', height);

		wrapper.style.width = width + 'px';
		canvas.className = 'MrClayGraphC';
		wrapper = canvas = null;
		return this._i;
	}
	,identity : function (n) {return n;}
	,getSteps : function (spec) {
		// not an ideal implementation; as the graph is farther off-center and with
		// smaller steps, more cycles are wasted getting to the viewport.
		var i,
		    set = [];
		if (spec.upper >= 0 && spec.lower <= 0) {
			// range has 0, extend outward both sides
			set.push(0);
			i = spec.step;
			while (i < spec.upper) {
				set.push(i);
				i += spec.step;
			}
			i = -spec.step;
			while (i > spec.lower) {
				set.push(i);
				i -= spec.step;
			}
			return set;

		} else if (spec.lower > 0) {
			// range in positives, start 0, go up
			i = 0;
			while (i < spec.upper) {
				if (i >= spec.lower) {
					set.push(i);
				}
				i += spec.step;
			}
			return set;

		} else {
			// range in negatives, start 0, go down
			i = 0;
			while (i > spec.lower) {
				if (i <= spec.upper) {
					set.push(i);
				}
				i -= spec.step;
			}
			return set;
		}
	}
};

/**
 * Constructor for Graph objects (creates a canvas element and wrapper div
 */
MrClay.Graph = function(spec) {
	this._cHeight = spec.height || MrClay.Graph_Default.height;
	this._cWidth = spec.width || MrClay.Graph_Default.width;
	this._i = MrClay.Graph_Util.createElements(spec.parentId, this._cWidth, this._cHeight);
	this.context = document.getElementById('MrClayGraphC' + this._i).getContext('2d');
	this._pxCenter = [Math.round(this._cWidth / 2), Math.round(this._cHeight / 2)];
	this._xPixelWidth = 1.02;
	this._unitOffset = [0, 0];
	this._unit2Px = [1, 1];
	this._scaleX = 1;
	this._scaleY = 1;
	this._plotting = null;
	this._plotMaxY = null;
	this._plotMinY = null;
	if (spec.visibleXUnits) {
		this.setVisibleXUnits(spec.visibleXUnits);
	}
	if (spec.visibleYUnits) {
		this.setVisibleYUnits(spec.visibleYUnits);
	}
	if (spec.scaleY) {
		this.setScaleY(spec.scaleY);
	}
	if (spec.scaleX) {
		this.setScaleX(spec.scaleX);
	}
	if (spec.center) {
		this.setCenter(spec.center[0], spec.center[1]);
	}
};

MrClay.Graph.prototype = {
    getElement : function () {
    	document.getElementById('MrClayGraphC' + this._i).getContext('2d');
    }
    ,addContext : function (props) {
    	for (var prop in props) {
    		this.context[prop] = props[prop];
    	}
    }
    ,addAxisLabel : function (spec) {
    	var div = document.createElement('div')
    		,inner = div.appendChild(document.createElement('div'));
    	div.className = 'mcg_' + spec.side;
    	if ('left' == spec.side || 'right' == spec.side) {
    		// left/right
    		div.style.top = this.y(spec.y) + 'px';
    		if (typeof spec.html == 'undefined') {
    			spec.html = spec.y;
    		}
    	} else {
    		// top/bottom
    		var m = this._pxCenter[0] - this.x(spec.x);
    		inner.style.right = m + 'px';
    		if (typeof spec.html == 'undefined') {
    			spec.html = spec.x;
    		}
    	}
    	document.getElementById('MrClayGraphW' + this._i).appendChild(div);
    	inner.innerHTML = spec.html;
    
    	div = inner = null;
    }
    ,getElementIds : function () {
    	return {
    		canvas : 'MrClayGraphC' + this._i
    		,wrapper : 'MrClayGraphW' + this._i
    	};
    }
    ,beginPlot : function () {
    	this._plotMaxY = this.invY(0 - this._cHeight);
    	this._plotMinY = this.invY(this._cHeight * 2);
    	this._plotting = false;
    }
    ,plotPoint : function (x, y) {
    	if (!isNaN(y) && isFinite(y) 
            && (y > this._plotMinY && y < this._plotMaxY)) {
    		if (this._plotting) {
    			this._continueLine(x, y);
    		} else {
    			this._beginLine(x, y);
    			this._plotting = true;
    		}
    	} else {
    		if (this._plotting) {
    			this._endLine();
    			this._plotting = false;
    		}
    	}
    }
    ,endPlot : function () {
    	if (this._plotting) {
    		this._endLine();
    	}
    }
    ,_isNum : function(val) {
    	return !!(typeof val == 'number');
    }
    ,setVisibleYUnits : function (units) {
    	this._unit2Px = [
    		Math.round(this._cHeight / units * this._xPixelWidth)
    		,Math.round(this._cHeight / units)
    	];
    }
    ,setVisibleXUnits : function (units) {
    	this._unit2Px = [
    		Math.round(this._cWidth / units)
    		,Math.round(this._cWidth / units / this._xPixelWidth)
    	];
    }
    ,setScaleX : function (scale) {
    	this._scaleX = scale;
    }
    ,setScaleY : function (scale) {
    	this._scaleY = scale;
    }
    ,setCenter : function (x, y) {
    	this._unitOffset = [-x * this._scaleX, -y * this._scaleY];
    }
    ,x : function (coorX) {
    	return this._pxCenter[0]
    		+ (coorX * this._scaleX + this._unitOffset[0]) * this._unit2Px[0];
    }
    ,invX : function(x) {
    	return (x - this._pxCenter[0] - this._unitOffset[0] * this._unit2Px[0])
    		/ (this._scaleX * this._unit2Px[0]);
    }
    ,y : function (coorY) {
    	return this._pxCenter[1] - (coorY * this._scaleY + this._unitOffset[1]) * this._unit2Px[1];
    }
    ,invY : function(y) {
    	return (this._pxCenter[1] - this._unitOffset[1] * this._unit2Px[1] - y)
    		/ (this._scaleY * this._unit2Px[1]);
    }
/**
 * Connect given points with a stroke. Use nulls in place of points to break into
 * multiple lines.
 */
    ,connectPoints : function (points) {
    	var lineBegun = false;
    	for (var i = 0, l = points.length; i < l; ++i) {
    		if (null === points[i]) {
    			if (lineBegun) {
    				this._endLine();
    				lineBegun = false;
    			}
    		} else {
    			if (!lineBegun) {
    				this._beginLine(points[i][0], points[i][1]);
    				lineBegun = true;
    			} else {
    				this._continueLine(points[i][0], points[i][1]);
    			}
    		}
    	}
    	if (lineBegun) {
    		this._endLine();
    	}
    }
    ,_beginLine : function (x, y) {
    	this.context.beginPath();
    	this.context.moveTo(this.x(x), this.y(y));
    }
    ,_continueLine : function (x, y) {
    	this.context.lineTo(this.x(x), this.y(y));
    }
    ,_endLine : function () {
    	this.context.stroke();
    	this.context.closePath();
    }
    ,_deltaSlope : function (y0, y1, y2, x) {
    	return (y2 - (2 * y1) + y0) / x;
    }
    ,inGraph : function (x, y) {
    	x = this.x(x);
    	y = this.y(y);
    	return !!(x >= 0 && y >= 0 && x < this._cWidth && y < this._cHeight);
    }
    ,plotFunction : function (obj) {
    	obj.step = obj.step || (this.invX(1) - this.invX(0));
    	var edges = this.getVisibleEdges()
    		,x;
    	if (! this._isNum(obj.from)) {
    		obj.from = edges[0][0];
    	}
    	if (! this._isNum(obj.to)) {
    		obj.to = edges[1][0];
    	}
    	x = obj.from;
    	this.beginPlot();
    	while (x < obj.to) {
    		this.plotPoint(x, obj.f(x));
    		x += obj.step;
    	}
    	this.endPlot();
    }
    // caches within instance
    ,_getSteps : function (spec) {
    	if (!this._getStepsCache) {
    		this._getStepsCache = {};
    	}
    	var cacheId = spec.step + '|' + spec.upper + '|' + spec.lower;
    	if (!this._getStepsCache[cacheId]) {
    		this._getStepsCache[cacheId] = MrClay.Graph_Util.getSteps(spec);
    	}
    	return this._getStepsCache[cacheId];
    }
    ,drawGrid : function (spec) {
    	spec = spec || {};
    	if (typeof spec.xLinesEvery == 'undefined') {
    		spec.xLinesEvery = Math.abs(Math.round(
    			this.invX(MrClay.Graph_Default.gridSpacingX) - this.invX(0)
    		));
    	}
    	if (typeof spec.yLinesEvery == 'undefined') {
    		spec.yLinesEvery = Math.abs(Math.round(
    			this.invY(MrClay.Graph_Default.gridSpacingY) - this.invY(0)
    		));
    	}
    
    
    	this.context.lineWidth = (spec.lineWidth || MrClay.Graph_Default.gridWidth);
    	this.context.strokeStyle = (spec.strokeStyle || MrClay.Graph_Default.gridStroke);
    
    	var edges = this.getVisibleEdges();
    	var set, i, l;
    
    	// x lines
    	if (spec.xLinesEvery) {
    		set = this._getSteps({
    			upper : edges[1][0]
    			,lower : edges[0][0]
    			,step : spec.xLinesEvery
    		});
    		for (i = 0, l = set.length; i < l; ++i) {
    			this.connectPoints([
    				[set[i], edges[0][1]]
    				,[set[i], edges[1][1]]
    			]);
    		}
    	}
    	// x labels
    	if (spec.xLabelsEvery) {
    		set = this._getSteps({
    			upper : edges[1][0]
    			,lower : edges[0][0]
    			,step : spec.xLabelsEvery
    		});
    		spec.xLabelHtml = spec.xLabelHtml || MrClay.Graph_Util.identity;
    		for (i = 0, l = set.length; i < l; ++i) {
    			this.addAxisLabel({
    				side : 'bottom'
    				,x : set[i]
    				,html : spec.xLabelHtml(set[i])
    			});
    		}
    	}
    
    	// y lines
    	if (spec.yLinesEvery) {
    		set = this._getSteps({
    			upper : edges[1][1]
    			,lower : edges[0][1]
    			,step : spec.yLinesEvery
    		});
    		for (i = 0, l = set.length; i < l; ++i) {
    			this.connectPoints([
    				[edges[0][0], set[i]]
    				,[edges[1][0], set[i]]
    			]);
    		}
    	}
    	// y labels
    	if (spec.yLabelsEvery) {
    		set = this._getSteps({
    			upper : edges[1][1]
    			,lower : edges[0][1]
    			,step : spec.yLabelsEvery
    		});
    		spec.yLabelHtml = spec.yLabelHtml || MrClay.Graph_Util.identity;
    		for (i = 0, l = set.length; i < l; ++i) {
    			this.addAxisLabel({
    				side : 'left'
    				,y : set[i]
    				,html : spec.yLabelHtml(set[i])
    			});
    		}
    	}
    }
    ,drawAxes : function (spec) {
    	spec = spec || {};
    	this.context.lineWidth = (spec.lineWidth || MrClay.Graph_Default.axesWidth);
    	this.context.strokeStyle = (spec.strokeStyle || MrClay.Graph_Default.axesStroke);
    	var edges = this.getExtendedEdges();
    	this.connectPoints([[0, edges[0][1]], [0, edges[1][1]]]);
    	this.connectPoints([[edges[0][0], 0], [edges[1][0], 0]]);
    }
    ,getVisibleEdges : function () {
    	return [
    		[this.invX(0), this.invY(this._cHeight)]
    		,[this.invX(this._cWidth), this.invY(0)]
    	];
    }
    ,getExtendedEdges : function () {
    	var edges = this.getVisibleEdges();
    	// extend outward
    	return [
    		[Math.floor(edges[0][0]), Math.floor(edges[0][1])]
    		,[Math.ceil(edges[1][0]), Math.ceil(edges[1][1])]
    	];
    }
};