var overDropMenu = null;
Event.observe(window, 'load', function(evt) {
	var subs = $$('.popup li .navlist').each(function(s) {
		s.style.display = 'none';
		Event.observe(s.parentNode, 'mouseover', function(evt) {
			var top = Event.element(evt).up('li');
			if (top) {
				var sub = top.down('.navlist');
				if (sub) {
					if ( (overDropMenu) && (overDropMenu != top) ) {
						overDropMenu.down('.navlist').fade(1, 0, 125, {
							onComplete: function(el) {
								el.style.display = 'none';
							}
						});
					}
					if (sub.style.display != 'block') {
						sub.fade(0, 1, 125);
						sub.style.display = 'block';
					}
					overDropMenu = top;
				}
			}
		});
	});
	Event.observe(document.documentElement, 'mousemove', function(evt) {
		if (overDropMenu) {
			var px = Event.pointerX(evt);
			var py = Event.pointerY(evt);
			var top = overDropMenu;
			var sub = top.down('.navlist');
			if (top && sub) {
				var os = top.cumulativeOffset();
				if ( (os.left <= px) && (os.left + top.getWidth() >= px) && (os.top <= py) && (os.top + top.getHeight() >= py) ) {
					return;
				}
				os = sub.cumulativeOffset();
				if ( (os.left <= px) && (os.left + sub.getWidth() >= px) && (os.top <= py) && (os.top + sub.getHeight() >= py) ) {
					return;
				}
				sub.fade(1, 0, 125, {
					onComplete: function(el) {
						el.style.display = 'none';
					}
				});
				overDropMenu = null;
			}
		}
	});
});
