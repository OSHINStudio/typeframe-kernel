jQuery.fn.loadAndEval = function(url, data, complete) {
	var $ = jQuery;
	var targ = $(this);
	var parts = url.split(' ', 2);
	$.ajax(parts[0], {
		success: function(data, textStatus, xmlHttpRequest) {
			var placeholder = $('<div/>');
			targ.replaceWith(placeholder);
			var tmp = $(data);
			tmp.css({display: 'none'});
			$('body').append(tmp);
			if (parts.length > 1) {
				placeholder.replaceWith(tmp.find(parts[1]));
				tmp.detach();
			} else {
				placeholder.replaceWith(tmp);
			}
			if (complete) {
				complete(data, textStatus, xmlHttpRequest);
			}
		}
	});
}
