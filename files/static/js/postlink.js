/**
 * Typeframe PostLink function
 *
 * Makes an anchor tag POST using a hidden form,
 * instead of GETing the traditional way.
 * Supports an (optional) "confirm" popup.
 */

var PostLink = function(link, url, confirmText)
{
	var $link, $form, postParams, i, l, k;

	$link = $(link);
	$form = $('<form/>');
	$form.attr('action', url);
	$form.attr('method', 'post');
	$form.css('display', 'none');
	$link.after($form);

	postParams = {};
	if (url.indexOf('?') != -1)
	{
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
		$form.append(inp);
	}

	$link.click(function(evt)
	{
		evt.preventDefault();
		if (!confirmText || confirm(confirmText))
			$form.submit();
	});
}
