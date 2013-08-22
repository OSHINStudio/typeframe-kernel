/***********************************************************
	CoreFX Library

	Required files:
	* prototype.js (http://prototypejs.org)

	Author: Fred Snyder
	Company: Castwide Technologies
	URL: http://castwide.com
	Date Created: July 23, 2008
	Date Modified: August 27, 2008

	8-27-2008	Added transition() method

***********************************************************/

var CoreFX = new function() {
	/*
		Granularity is the number of milliseconds between animation frames.
		The lower the granularity, the greater the frames per second.
		50 is a good value for smooth animation, as it yields 20 fps.
		If animations take longer than they should, a good compromise is a
		granularity of 125, which yields 8 fps, a rate that is known in the
		animation industry as the Yogi Bear Factor (or at least it should be).
	*/
	var granularity = 50;
	var _move = function(elem, x, y) {
		if (elem.getStyle('position') == 'absolute') {
			elem.style.left = x + 'px';
			elem.style.top = y + 'px';
		} else {
			elem.absolutize();
			elem.style.left = x + 'px';
			elem.style.top = y + 'px';
			// TODO: Relativizing tr elements makes them behave as if
			// statically positioned in Firefox.
			//if (elem.tagName.toLowerCase() != 'tr') {
				elem.relativize();
			//}
		}
	}
	this.Mover = function(elem, endX, endY, millisec, params) {
		elem = $(elem);
		var frameStack = new Array();
		var start = elem.positionedOffset();
		var totalFrames = Math.ceil(millisec / granularity);
		var moveX = ( (endX - start[0]) / totalFrames );
		var moveY = ( (endY - start[1]) / totalFrames );
		var curX = start[0];
		var curY = start[1];
		if (params) {
			var onFrame = params.onFrame;
			var onComplete = params.onComplete;
		}
		var move = function() {
			var cur = frameStack.shift();
			if (onFrame) {
				onFrame(elem);
			}
			_move(elem, cur[0], cur[1]);
			if ( (frameStack.length == 0) && (onComplete) ) {
				onComplete(elem);
			}
		}
		for (var i = 0; i < totalFrames; i++) {
			if (i == totalFrames - 1) {
				curX = endX;
				curY = endY;
			} else {
				curX += moveX;
				curY += moveY;
			}
			frameStack.push([curX, curY]);
			setTimeout(move, granularity * i);
		}
	}
	this.Fader = function(elem, start, end, millisec, params) {
		elem = $(elem);
		var frameStack = new Array();
		var totalFrames = Math.ceil(millisec / granularity);
		var perFrame = (end - start) / totalFrames;
		cur = start;
		if (params) {
			var onFrame = params.onFrame;
			var onComplete = params.onComplete;
		}
		var fade = function() {
			var cur = frameStack.shift();
			if (onFrame) {
				onFrame(elem);
			}
			elem.setOpacity(cur);
			if ( (frameStack.length == 0) && (onComplete) ) {
				onComplete(elem);
			}
		}
		for (var i = 0; i < totalFrames; i++) {
			if (i == totalFrames - 1) {
				cur = end;
			} else {
				cur += perFrame;
			}
			frameStack.push(cur);
			setTimeout(fade, granularity * i);
		}
	}
	this.Transer = function(elem, attrib, start, end, unit, millisec, params) {
		elem = $(elem);
		var frameStack = new Array();
		var totalFrames = Math.ceil(millisec / granularity);
		var offset = ( (end - start) / totalFrames );
		if (params) {
			var onFrame = params.onFrame;
			var onComplete = params.onComplete;
		}
		var change = function() {
			var cur = frameStack.shift();
			if (onFrame) {
				onFrame(elem);
			}
			elem.setStyle(cur);
			if ( (frameStack.length == 0) && (onComplete) ) {
				onComplete(elem);
			}
		}
		val = start;
		for (var i = 0; i < totalFrames; i++) {
			if (i == totalFrames - 1) {
				cur = new Object();
				cur[attrib] = end + unit;
			} else {
				val += offset;
				cur = new Object();
				cur[attrib] = val + unit;
			}
			frameStack.push(cur);
			setTimeout(change, granularity * i);
		}
	}
	this.distance = function(x1, y1, x2, y2) {
		return Math.sqrt( Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2) );
	}
	// Methods to include in Prototype elements
	this.Methods = {
		move: function(element, x, y, millisec, params) {
			element = $(element);
			if (!millisec && !params) {
				_move(element, x, y);
			} else {
				new CoreFX.Mover(element, x, y, millisec, params);
			}
		},
		fade: function(element, start, end, millisec, params) {
			element = $(element);
			if (!millisec && !params) {
				elem.setOpacity(end);
			} else {
				if (start === null) {
					start = element.getStyle('opacity');
				}
				new CoreFX.Fader(element, start, end, millisec, params);
			}
		},
		transition: function(element, attrib, start, end, unit, millisec, params) {
			element = $(element);
			if (!millisec && !params) {
				var obj = new Object();
				obj[attrib] = end;
				element.setStyle(obj);
			} else {
				if (start === null) {
					start = element.getStyle(attrib);
				}
				new CoreFX.Transer(element, attrib, start, end, unit, millisec, params);
			}
		}
	}
}
Element.addMethods(CoreFX.Methods);

var CoreDND = new function() {
	var dragMap = {};
	var curDragger;
	var curX;
	var curY;
	var startDrag = function(e) {
		var target = Event.element(e);
		var draggee = dragMap[target.identify()];
		while (!draggee) {
			target = target.parentNode;
			if (!target) break;
			draggee = dragMap[target.identify()];
		}
		if (draggee) {
			curDragger = target;
			curX = Event.pointerX(e);
			curY = Event.pointerY(e);
			Event.stop(e);
		}
	}
	var updateDrag = function(e) {
		if (curDragger) {
			var draggee = dragMap[curDragger.identify()];
			if (draggee) {
				Event.stop(e);
				var container = draggee[0];
				var moveX = Event.pointerX(e) - curX;
				var moveY = Event.pointerY(e) - curY;
				container.move(container.positionedOffset()[0] + moveX, container.positionedOffset()[1] + moveY);
				curX = Event.pointerX(e);
				curY = Event.pointerY(e);
			} else {
				curDragger = null;
			}
		}
	}
	var stopDrag = function(e) {
		if (curDragger) {
			var draggee = dragMap[curDragger.identify()];
			if (draggee && draggee[1]) {
				if (draggee[1].onDrop) {
					draggee[1].onDrop(draggee[0]);
				}
				if (draggee[1].reposition) {
					draggee[0].style.left = null;
					draggee[0].style.top = null;
					draggee[0].style.position = 'static';
				}
			}
			curDragger = null;
			Event.stop(e);
		}
	}
	this.enable = function(draggee, dragger, params) {
		/*
		Arguments:
			draggee - the element to be dragged
			dragger - the child element that initiates the drag when clicked
			(dragger and draggee can be the same element)
			params (optional) - onDrop event and reposition boolean
		*/
		draggee = $(draggee);
		dragger = $(dragger);
		if ( (dragger != draggee) && (!dragger.descendantOf(draggee)) ) {
			throw new Error('Drag button must be child of draggable element');
			return;
		}
		dragMap[dragger.identify()] = [draggee, params];
		Event.observe(dragger, 'mousedown', startDrag);
	}
	this.disable = function(dragger) {
		unset(dragMap[dragger.identify()]);
		Event.stopObserving(dragger, 'mousedown', startDrag);
	}
	this.dragging = function() {
		if (curDragger) {
			return dragMap[curDragger.identify()];
		}
		return null;
	}
	Event.observe(document, 'mousemove', updateDrag.bindAsEventListener(CoreDND));
	Event.observe(document, 'mouseup', stopDrag.bindAsEventListener(CoreDND));
}
