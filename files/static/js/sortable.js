/***********************************************************
	Sortable Library

	Required files:
	* prototype.js (http://prototypejs.org)
	* corefx.js (http://castwide.com)

	Author: Fred Snyder
	Company: Castwide Technologies
	URL: http://castwide.com
	Date Created: July 24, 2008

***********************************************************/

var Sortable = new function() {
	var contForms = {};
	this.enable = function(container, itmRule, btnRule, submitForm) {
		container = $(container);
		submitForm = $(submitForm);
		if (container.nodeName == 'TABLE') {
			SortableTable.enable(container, itmRule, btnRule, submitForm);
		} else {
			contForms[container.identify()] = submitForm;
			$$(itmRule).each(function(itm) {
				if (itm.descendantOf(container)) {
					if (itm.down(btnRule)) {
						var btn = itm.down(btnRule);
						CoreDND.enable(itm, btn, {
							onDrop: function(e) {
								var sibs = e.siblings();
								var inserted = false;
								for (var i = 0; i < sibs.length; i++) {
									var s = sibs[i];
									if (e.positionedOffset()[1] < s.positionedOffset()[1]) {
										s.parentNode.insertBefore(e, s);
										inserted = true;
										break;
									}
								}
								if (!inserted) {
									s.parentNode.appendChild(e);
								}
								var p = e.parentNode;
								while (p) {
									if (typeof(contForms[p.identify()]) != 'undefined') {
										if (contForms[p.identify()].submit) {
											contForms[p.identify()].submit();
										}
										break;
									}
									p = p.parentNode;
								}
							},
							reposition: true
						});
					}
				}
			});
		}
	}
}

var SortableTable = new function() {
	var selectedRow;
	var contForms = {};
	var activeButton = null;
	var checkRow = function(e) {
		if (!selectedRow) {
			var tbl = Event.findElement(e, 'table');
			if (tbl._btns.indexOf(Event.element(e)) == -1) {
				return;
			}
			var el = Event.element(e);
			if (el.nodeName != 'TR') el = el.up('tr');
			if (el) {
				selectedRow = el;
				selectedRow.addClassName('selected');
				activeButton = Event.element(e);
				Event.stop(e);
			}
		}
	}
	var updateRowSort = function(e) {
		if (selectedRow) {
			var y = Event.pointerY(e);
			if ( (y < selectedRow.positionedOffset()[1]) || (y > selectedRow.positionedOffset()[1] + selectedRow.getHeight()) ) {
				var prv = selectedRow.previous();
				var nxt = selectedRow.next();
				var moved = false;
				if (prv) {
					// TODO: This is a nasty hack that assumes we should not
					// consider the previous row a candidate for movement
					// if it does not contain a "mover class" element
					if (activeButton.className && (prv.down('.' + activeButton.className))) {
						if (y < prv.cumulativeOffset()[1] + prv.getHeight()) {
							if ( (prv.getHeight() <= selectedRow.getHeight()) || (y < prv.cumulativeOffset()[1] + selectedRow.getHeight()) ) {
								selectedRow.parentNode.insertBefore(selectedRow, prv);
								moved = true;
							}
						}
					}
				}
				if (nxt && !moved) {
					if (y > nxt.cumulativeOffset()[1]) {
						if (nxt.next()) {
							nxt.parentNode.insertBefore(selectedRow, nxt.next());
						} else {
							nxt.parentNode.appendChild(selectedRow);
						}
						moved = true;
					}
				}
			}
			Event.stop(e);
		}
	}
	var stopRowSort = function(e) {
		if (selectedRow) {
			selectedRow.removeClassName('selected');
			// This ridiculous hack is necessary for IE because the
			// sort makes children of the TR elements unselectable
			if (selectedRow.outerHTML) {
				tbl = selectedRow.up('table');
				var par = tbl.parentNode;
				var nxt = tbl.nextSibling;
				par.removeChild(tbl);
				par.insertBefore(tbl, nxt);
			}
			var p = Element.extend(selectedRow.parentNode);
			while (p) {
				if (p.identify) {
					if (contForms[p.identify()]) {
						if (contForms[p.identify()].submit) {
							contForms[p.identify()].submit();
						}
						break;
					}
				}
				p = Element.extend(p.parentNode);
			}
		}
		selectedRow = null;
		activeButton = null;
	}
	this.enable = function(tbl, rowRule, btnRule, submitForm) {
		tbl = $(tbl);
		submitForm = $(submitForm);
		contForms[tbl.identify()] = submitForm;
		tbl._btns = new Array();
		if (!rowRule) rowRule = 'tr';
		tbl.select(rowRule).each(function(tr) {
			var btn = tr;
			if (btnRule) btn = tr.down(btnRule);
			tbl._btns.push(btn);
		});
		Event.observe(tbl, 'mousedown', checkRow.bindAsEventListener(SortableTable));
	}
	Event.observe(document, 'mousemove', updateRowSort.bindAsEventListener(SortableTable));
	Event.observe(document, 'mouseup', stopRowSort.bindAsEventListener(SortableTable));
}
