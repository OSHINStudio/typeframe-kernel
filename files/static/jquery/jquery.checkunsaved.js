jQuery.fn.checkUnsaved = function(message, options) {
	if (!message) message = true;
	var $ = jQuery;
	var confirmUnload = function(on) {
		window.onbeforeunload = (on) ? unloadMessage : null;
	};
	var unloadMessage = function() {
		return message;
	};
	var inputs = $(this).find(':input');
	if (options.ignore) {
		inputs = inputs.not(options.ignore);
	}
	inputs.bind("change", function() {
		confirmUnload(true);
	});
	$(this).submit(function() {
		confirmUnload(false);
	});
}
