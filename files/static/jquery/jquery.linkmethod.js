/**
 * linkmethod
 */

new function() {
	var $ = jQuery;
	var _generatePostForm = function(link) {
		var form = $('<form/>');
		form.attr('method', 'post');
		form.css('display', 'none');
		var postParams = {};
		var url = link.attr('href');
		if (url.indexOf('?') > -1) {
			var arr = url.substr(url.indexOf('?') + 1).split('&');
			for (i = 0, l = arr.length; i < l; ++i)
			{
				var parts = arr[i].split('=');
				postParams[parts[0]] = parts[1];
			}
			url = url.substr(0, url.indexOf('?'));
		}
		for (k in postParams) {
			var inp = $('<input/>');
			inp.attr('type', 'hidden');
			inp.attr('name', k);
			inp.attr('value', postParams[k]);
			form.append(inp);
		}
		form.attr('action', url);
		link.after(form);
		return form;
	};
	var _click = function(evt) {
		if ($(this).attr('data-confirm')) {
			if (!confirm($(this).attr('data-confirm'))) {
				evt.preventDefault();
				return;
			}
		}
		if ($(this).attr('rel')) {
			var parts = $(this).attr('rel').split(' ');
			if (parts.indexOf('post') > -1) {
				if (parts.indexOf('ajax') > -1) {
					// TODO: AJAX post
					evt.preventDefault();
				} else {
					// Normal post
					evt.preventDefault();
					var form = _generatePostForm($(this));
					form.submit();
				}
			}
			if (parts.indexOf('ajax') > -1) {
				// TODO: AJAX get
				evt.preventDefault();
			}
		}
	};
	$(function() {
		$('a').on('click', _click);
	});
};
