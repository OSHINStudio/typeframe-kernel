/*
 * Typeframe headerSuccess function
 *
 * Displays a message in the specified element.
 * Options include the selector, fade-in time,
 * hold time, and fade-out time.
*/

// define typef object if undefined
if (!typef) var typef = {};

// define header success function
typef.headerSuccess = function(message, options)
{
	options = $.extend(
	{
		selector:    '#redirect_message',
		fadeInTime:  800,
		holdTime:    1200,
		fadeOutTime: 800
	}, (options || {}));

	var $element = $(options.selector);
	if (!$element) return;

	$element.html('<p>' + message + '</p>');
	$element.hide().fadeIn(options.fadeInTime, function()
	{
		setTimeout(function()
		{
			$element.fadeOut(options.fadeOutTime);
		}, options.holdTime);
	});
};
