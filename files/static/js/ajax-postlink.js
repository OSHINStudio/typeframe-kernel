/**
 * Typeframe ajaxPostLink function
 *
 * Makes an anchor tag POST using AJAX,
 * instead of GETing the traditional way.
 * Supports an (optional) "confirm" popup.
 * Provides a custom event (postLink-
 * Success), which is called after POSTing
 * the data.
 */

// define typef object if undefined
if (!typef) var typef = {};

// define postlink function
typef.ajaxPostLink = function(root, selector)
{
	if (!root)     root     = 'body';
	if (!selector) selector = 'a.postlink';

	$(root).delegate(selector, 'click', function()
	{
		// define variables
		var url, href, vars, $a;

		// get link
		$a = $(this);

		// confirm user's desire to submit link
		if ($a.attr('confirm') && !confirm($a.attr('confirm')))
			return false;

		// transform URL
		url  = $a.attr('href').split('?');
		href = url[0];
		vars = ('' + url[1]);

		// submit link
		$.post(href, vars, function(data)
		{
			$a.trigger('ajaxPostLinkSuccess', [data, $a]);
		},
		'json');

		// prevent default behavior
		return false;
	});
};

// enable function globally when dom loaded
$(function() { typef.ajaxPostLink(); });
