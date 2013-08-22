/**
 * jQuery Dyneditables
 *
 * History
 * Version 1.4.0 2011.11.03
 *     Added support for arrays to be used as option sets.  This is useful if there doesn't need to be a key/title pair.
 *     Made the edit link float to the right.  This makes it jump around less with varying lengths of data.
 *     Added support for specifying the column width via the 'width' key in the option configuration.
 *     Switched from live() to delegate() for a couple events.  This should yield better performance.
 *     Fixed a :selected bug for select options.
 *     Fixed a bug where new entries added were not immedaiately draggable.
 *
 * Version 1.3.2 2011.10.24
 *     Fixed bug with checkboxes
 *
 * Version 1.3.1 2010.11.18
 *     Fixed issue with multiple file upload options existing in the same dyneditable form.
 *
 * Version 1.3.0 2010.11.08:
 *     Fixed a bug with IE/Chrome uploader.
 *
 * Version 1.2.0 2010.10.10:
 *     It seems to work decently well.
 *
 * @author Charlie Powell
 * @copyright BAM 2010
 */

function com_blindacre_dyneditables(o, d){

	// Any options currently set for this object.
	o = o || {};
	var options = jQuery.extend({
		hascreate: true,
		data: {
			id: 'id',
			datatype: 'post',
			columns: [],
			radios: [],
			formname_new: 'new',
			formname_del: 'del'
		},
		images: {
			add: "/images/buttons/add.png",
			cancel: "/images/buttons/delete.png",
			del: "/images/buttons/delete.png",
			edit: "/images/buttons/edit.png",
			ok: "/images/buttons/selected.png",
			draghandle: '/images/buttons/grip.png'
		},
		speed: 250,
		uploadurl: false
	}, o);
	// @todo Make the class names settable too.

	var target = null;

	var data = d;

	this.bind = function(element){
		target = $(element);
		if(target[0].tagName != 'TABLE'){
			// It's not a table... WHAT?!?... It needs to be!
			target = $('<table/>');
			$(element).append(target);
		}
		// Make a referral back to this object.
		$(element).dyneditable = this;
	};

	var resetViewableRecords = function(){

		// Set every "edit" as hidden ane every "view" as visible.
		target.find('.editattributes').slideUp(options.speed, function(){ target.find('.viewattributes').slideDown(options.speed); });

		//target.find('.createattributes').slideDown(options.speed);
		target.find('.createattributes').css('visibility', 'visible');

		// Display the toolbars again too.
		target.find('.toolbars').find('*').css('visibility', 'visible');
	};

	// Will cancel any/all editable records currently in edit mode.
	// Will also reset the data back to default, or at least whatever it was before editing started.
	var cancelAllEditableRecords = function(){
		target.find('.editattributes').find('input[prevval]').each(function(){
			$(this).val($(this).attr('prevval'));
			$(this).removeAttr('prevval');
		});
	};

	var toggleEditableRecords = function(el){
		// Ensure every edit is hidden and cancelled first.
		cancelAllEditableRecords();
		target.find('.editattributes').hide();
		target.find('.toolbars').find('*').css('visibility', 'hidden');

		// Hide any create attributes.
		//target.find('.createattributes').slideUp(options.speed);
		target.find('.createattributes').css('visibility', 'hidden');

		// This event should be triggered on something inside a table data... lookup that column.
		$col = $(el).closest('td');

		$others = target.find('.viewattributes').not($col.find('.viewattributes'));

		if($others.length){
			$others.slideDown(options.speed, function(){
				$col.find('.viewattributes').slideUp(options.speed, function(){
					$col.find('.editattributes').slideDown(options.speed);
				});
			});
		}
		else{
			$col.find('.viewattributes').slideUp(options.speed, function(){
				$col.find('.editattributes').slideDown(options.speed);
			});
		}
		var $inp = $col.find(':input');

		// Remember the previous value... in case the user wants to hit cancel.
		$inp.attr('prevval', $inp.val());

		// Set focus to the input after any/all animations have completed.
		setTimeout(function(){ $inp.focus(); }, options.speed * 2 + 5);
	};

	var saveEditableRecord = function(el){

		// This event should be triggered on something inside a table row... lookup that row.
		var $col = $(el).closest('td');
		var $inp = $col.find(':input');

		// No longer need the previous value...
		$inp.removeAttr('prevval');

		// Each table row should consist of 1 or more td's, each with one input and one 'viewattribute', or better yet, one 'viewattributecontents'.
		if($col.find('.viewattributes').length){
			dst = $col.find('.viewattributecontents');
			if(!dst.length) dst = $col.find('.viewattributes');


			switch($inp[0].tagName){
				case 'SELECT':
					dst.html($inp.find('option:selected').html());
					break;
				default:
					// "input" is a pretty sizeable category....
					switch($inp.attr('type')){
						case 'checkbox':
							dst.html((($inp.attr('checked'))? _lookupColumn($inp.attr('dyn:key')).checkedtitle : _lookupColumn($inp.attr('dyn:key')).uncheckedtitle));
							break;
						case 'file':
							dst.html('Uploading...');
							uploadElement($inp, dst);
							break;
						default:
							dst.html($col.find('.editattributes').find(':input').val());
						break;
					}
			}
		}
	};

	////Upload the element to the server and return back the new filename.
	var uploadElement = function(el, targetel){
		//// Define the necessary elements for this subsystem.

		// The element itself, needs to be a jquery object.
		var $el = $(el);
		// I need to generate a unique id for the iframe.
		var iframeid = "uploadTargetFrame-" + $el.attr('dyn:key');
		var formid = "uploadAjaxieForm-" + $el.attr('dyn:key');
		// The iframe to be the target of the form submission.
		var $if = $('<iframe id="' + iframeid + '" name="' + iframeid + '" style="border:0px none; width:0px; height:0px;"></iframe>');
		// The form itself, containing all the metadata and the file itself.
		var $of = $('<form id="' + formid + '" target="' + iframeid + '" action="' + options.uploadurl + '" enctype="multipart/form-data" method="POST"></form>');
		// The target element to update after the data comes back.
		var $target = $(targetel).closest('td');

		// Attach the new elements onto the body... they need to be there in order to work.
		$('body').append($if);
		$('body').append($of);

		// Create a clone of the original element so I can attach the original onto the new form.
		$nel = $el.clone();
		$el.hide();
		$nel.insertAfter($el);

		// Set the new data on the original upload form.
		$el.attr('name', '___uploadfile');
		$el.appendTo($of);

		// And the necessary metadata.
		$of.append('<input type="hidden" name="basedir" value="' + $el.attr('dyn:basedir') + '"/>');

		// Attach the necessary events on the iframe so I know when its done loading.
		$if.load(function(){
			var data = $(this).contents().find("body").html();
			// Try to get the json data from the return..
			try{
				var json = jQuery.parseJSON(data);
				if(json.status == 1){
					// w00t, update the target data with the new filename.
					$target.find('.viewattributecontents').html(json.basename);
					$target.find('.editattributes').find('input[type=hidden]').val(json.basename);
				}
				else{
					$target.find('.viewattributecontents').html('ERROR');
					if(json.error) alert(json.error);
				}
			}
			catch(e){
				// Guess it's not json...
				$target.find('.viewattributecontents').html('ERROR');
				alert('Unexpected data returned from server.');
				if(typeof console != 'undefined'){
					console.log(data);
				}
			}

			// And remove the temporary objects since they're no longer useful...
			$if.remove();
			$of.remove();
		});

		$of.submit();
	};

	var saveNewRecord = function(el){
		// convert this record into a valid data object so I can feed it into the builder.
		var obj = {};
		var $row = $(el).closest('tr');
		// Add in the key.
		if(options.data.id){
			obj[options.data.id] = options.data.formname_new + '-' + Math.round(Math.random() * 5000);
		}
		for(var i in options.data.columns){
			$inp = $row.find(':input[name="___new[' + options.data.columns[i].key + ']"]');
			switch($inp.attr('type')){
				case 'checkbox':
					obj[options.data.columns[i].key] = $inp.attr('checked')? $inp.val() : '';
					break;
				case 'file':
					// This will get queued up as soon as the form's built and available.
					obj[options.data.columns[i].key] = $inp.val()? 'Uploading...' : 'None Uploaded';
					break;
				default:
					obj[options.data.columns[i].key] = $inp.val();
			}
		}
		var $html = $(_buildRecord(obj, 'new'));

		// Now that $html contains the HTML nodes of the new record...
		// I have something to pass along to upload so it knows what to update after it's done uploading.
		for(var i in options.data.columns){
			if(options.data.columns[i].type == 'image'){
				// UPLOAD IT!
				$inp = $row.find('input[name="___new[' + options.data.columns[i].key + ']"][type=file]');
				// (if there's a file selected...)
				if(!$inp.val()) continue;

				var inname = '';
				inname += options.data.name;
				//if(options.data.id) inname += '[' + datarow[options.data.id] + ']';
				inname += '[' + options.data.columns[i].key + ']';
				uploadElement($inp, $html.find(':input[name="' + inname + '"]'));
			}
		}

		// Reset the inputs to the default value.

		$row.find(':input').each(function(){
			$this = $(this);
			var def = _lookupColumn($this.attr('dyn:key')).defaultval || '';
			switch($this.attr('type')){
				case 'checkbox':
					$this.attr('checked', ($this.val() == def));
					break;
				default:
					$this.val(def);
					break;
			}
		});

		// Tack this new html into the table.
		$html.appendTo(target);

		// Don't forget the new events
		// I just need one event... Draggable?
		if(options.sortable) target.tableDnD({dragHandle: 'draghandle'});
	};

	var deleteRecord = function(el){
		// do something
		$row = $(el).closest('tr');
		// Rows that are new... easy!  Just remove them; no inputs needed.
		if($row.attr('dyn:status') == 'new'){
			$row.slideUp(options.speed, function(){ $row.remove(); });
		}
		// JSON datatypes should just remove the record completely.
		else if(options.data.datatype == 'json'){
			$row.slideUp(options.speed, function(){ $row.remove(); });
		}
		// Standard forms need to remember the ID for the post.
		else{
			// Add a hidden input to indicate this field was deleted to the form.
			var inname = options.data.name + '[' + options.data.formname_del + '-' + $row.attr('dyn:id') + ']';
			html = '<input type="hidden" name="' + inname + '" value="' + $row.attr('dyn:id') + '"/>';
			$(html).appendTo(target);
			$row.slideUp(options.speed, function(){ $row.remove(); });
		}

		// Ensure viewables are well... viewable.
		resetViewableRecords();
	};

	// This event is fired anytime a user clicks on the edit link via the view attributes.
	var editLinkClickEvent = function(e){
		toggleEditableRecords(e.currentTarget);
		return false;
	};

	// This event is fired anytime a user clicks the save link via the edit attributes.
	var saveLinkClickEvent = function(e){
		saveEditableRecord(e.currentTarget);
		resetViewableRecords();
		return false;
	};

	// This event is fired anytime a user clicks the cancel link via the edit attributes.
	var cancelLinkClickEvent = function(e){
		cancelAllEditableRecords();
		resetViewableRecords();
		return false;
	};

	var saveNewLinkClickEvent = function(e){
		saveNewRecord(e.currentTarget);
		return false;
	};

	var deleteLinkClickEvent = function(e){
		deleteRecord(e.currentTarget);
		return false;
	};

	var submitFormEvent = function(e){
		// Just check if there's something in the 'create new' field that needs to be saved.
		/*// Note, I need to run through every data column and check that.
		for(var i in options.data.columns){
			var d  = options.data.columns[i].default || "";
			var n  = options.data.columns[i].key;
			var $i = target.find('.createattributes').find(':input[name="___new[' + n + ']"');
			switch($i.attr('type')){
				case 'checkbox':
					if(d == $i.val() && !$i.attr('checked'))*/
		target.find('.createattributes').find('input').each(function(){
			var $inp = $(this);
			// Only check text inputs.
			if($inp.attr('type') != 'text') return true;

			if($inp.val() != ''){
				// Save It!
				saveNewRecord(this);
				// Returning false here doesn't exit the submit, just the jQuery each().
				return false;
			}
		});

		// Remove the ___new components under createattributes... these don't need to go along on the ride.
		target.find('.createattributes').find(':input').each(function(){
			$(this).remove();
		});

		// Remove any file component, those have been uploaded already.
		target.find('input[type="file"]').each(function(){
			$(this).remove();
		});

		// If the datatype is json... return raw json data for the form name instead of the form objects.
		if(options.data.datatype == 'json'){
			// Build the json encoded string from an object defined here.
			var jsonobj = [];
			target.find('tr').each(function(){
				$this = $(this);
				if($this.attr('dyn:status') == 'existing' || $this.attr('dyn:status') == 'new'){
					// Push this record's data onto the stack.
					var obj = {};
					$this.find(':input').each(function(){
						$inp = $(this);
						val = (($inp.attr('type') == 'checkbox') ?
								($inp.attr('checked') ? true : false) :
								 $inp.val());
						obj[$inp.attr('dyn:key')] = val;
					});
					jsonobj.push(obj);
				}
			});
			jsonobj = $.toJSON(jsonobj);

			// All the data has been gathered... remove the existing forms and replace them with one.
			target.find(':input').remove();

			//var $hinp = $('input[type=hidden]');
			var $hinp = $(document.createElement('input'));
			$hinp.attr('type', 'hidden');
			$hinp.attr('name', options.data.name);
			$hinp.val(jsonobj);
			$hinp.appendTo(target);
		}
		return true;
	};

	// This event is fired anytime the user keypresses the input via the edit attributes.
	// It is used to catch the [enter] and [esc] keys to have them trigger the appropriate functions.
	var inputKeyPressEvent = function(e){
		if(e.keyCode == 13){
			// This is needed because FF has a bit of an issue canceling from an input that's currently active... for some reason.....
			$tar = $(e.currentTarget).closest('td').find('.dynsavelink');
			setTimeout(function(){ $tar.click(); }, 2);
			return false;
		}
		if(e.keyCode == 27){
			// This is needed because FF has a bit of an issue canceling from an input that's currently active... for some reason.....
			$tar = $(e.currentTarget).closest('td').find('.dyncancellink');
			setTimeout(function(){ $tar.click(); }, 2);
			return false;
		}
		return true;
	};

	// This event is fired anytime the user keypresses the input via the create attributes.
	// It is used to catch the [enter] and [esc] keys to have them trigger the appropriate functions.
	var newInputKeyPressEvent = function(e){
		if(e.keyCode == 13){
			saveNewRecord(e.currentTarget);
			return false;
		}
		if(e.keyCode == 27){
			// This is needed because FF has a bit of an issue canceling from an input that's currently active... for some reason.....
			$tar = $(e.currentTarget).closest('tr').find('input');
			setTimeout(function(){ $tar.val(''); }, 2);
			return false;
		}
		return true;
	};

	this.build = function(){
		var out, x, colatts, i;

		// First, clear out any previous data.
		target.html('');
		// Build the new HTML.
		out = '';

		// Create the title record.
		if(options.hastitle){
			out += '<tr class="nodrop">';
			if(options.sortable){
				out += '<th>&nbsp;</th>';
			}
			for(x in options.data.columns){
				colatts = '';
				if(typeof options.data.columns[x].width != 'undefined') colatts += ' width=' + options.data.columns[x].width;

				out += '<th' + colatts + '>';
				if(options.data.columns[x].title) out += options.data.columns[x].title;
				else out += options.data.columns[x].key;
				out += '</th>';
			}
			// Final one for the toolbars.
			out += '<th>&nbsp;</th>';
			out += '</tr>';
		}

		// Create the 'Create New' records...
		if(options.hascreate){
			out += '<tr class="createattributes nodrop">';
			if(options.sortable){
				out += '<td>&nbsp;</td>';
			}

			for(x in options.data.columns){
				colatts = '';
				if(typeof options.data.columns[x].width != 'undefined') colatts += ' width=' + options.data.columns[x].width;

				out += '<td' + colatts + '>';

				if(options.data.columns[x].editprepend) out += options.data.columns[x].editprepend;

				// Build the actual input for the 'add new' record.
				out += _buildInputField('___new[' + options.data.columns[x].key + ']', options.data.columns[x]);

				out += '</td>';
			}
			for(x in options.data.groupradios){
				// Group Radio buttons don't get a default value on create.. they become editable upon adding.
				out += '<td>&nbsp;</td>';
			}

			out += '<td class="toolbars">';
			out += '<a href="#" class="dynsavenewlink"><img src="' + options.images.add + '" alt="Add" title="Add" /></a>';
			out += '</td>';

			out += '</tr>';
		}

		for(i in data){
			out += _buildRecord(data[i], 'existing');
		}


		out += '';

		target.html(out);

		if (undefined === com_blindacre_dyneditables.target_counter)
			com_blindacre_dyneditables.target_counter = 0;
		else
			++com_blindacre_dyneditables.target_counter;

		target_class = ('dyneditable-table-' + com_blindacre_dyneditables.target_counter);

		target.addClass(target_class);

		// And attach the events.
		_attachEvents();
	};

	var _lookupColumn = function(colname){
		// Will get the column data as per defined in the control data.
		// This is useful for functions such as "get 'my' default value...
		for(var i in options.data.columns){
			if(options.data.columns[i].key == colname) return options.data.columns[i];
		}

		return null;
	};

	var _buildInputField = function(name, columnobj, datarow){
		// This is the default input type.
		if(!columnobj.type) columnobj.type = 'text';

		// I need to determine the value from the datarow (or default if new).
		var val = '';
		if(typeof(datarow) == 'object'){
			// Existing Record
			val = (typeof datarow[columnobj.key] != 'undefined')? datarow[columnobj.key] : '';
		}
		else{
			// New Record
			val = columnobj.defaultval || '';
		}


		out = '';

		switch(columnobj.type){
			case 'select':
				out += '<select dyn:key="' + columnobj.key + '" name="' + name + '">';
				// Allow an array to be passed in.
				if(columnobj.options instanceof Array){
					for(var i = 0; i < columnobj.options.length; i++){
						out += '<option value="' + columnobj.options[i] + '"';
						if(val == o) out += ' selected';
						out += '>' + columnobj.options[i] + '</option>';
					}
				}
				// It's an object, so use each key and value.
				else{
					for(var o in columnobj.options){
						out += '<option value="' + o + '"';
						if(val == o) out += ' selected';
						out += '>' + columnobj.options[o] + '</option>';
					}
				}
				out += '</select>';
				break;
			case 'checkbox':
				out += '<input dyn:key="' + columnobj.key + '" type="checkbox" name="' + name + '" value="' + columnobj.value + '"';
				if(_issame(val, columnobj.value)) out += ' checked';
				out += '/>&nbsp;';
				out += columnobj.checkedtitle + '&nbsp;';
				break;
			case 'image':
				out += '<input type="file" dyn:key="' + columnobj.key + '" dyn:basedir="' + columnobj.basedir + '" name="' + name + '"/>';
				// Also attach the actual input form to contain the value, (since everything's uploaded automatically anyways).
				out += '<input type="hidden" dyn:key="' + columnobj.key + '" dyn:basedir="' + columnobj.basedir + '" name="' + name + '" value="' + val + '"/>';
				break;
			default:
				out += '<input dyn:key="' + columnobj.key + '" type="text" name="' + name + '" value="' + val + '"';
				if(columnobj.editsize) out += ' size="' + columnobj.editsize + '"';
				out += '/>';
		}

		return out;
	};

	var _issame = function(val1, val2){
		// Compare val1 and val2 in a non-typecast specific manner.
		// This is needed because the raw JS may provide a value as true, but that cannot be accurately reflected in the input value "true".
		// Some typecasting ill-logic before the type logic begins... "true" and true should match...
		if(val1 === val2) return true;
		if(val1 == val2) return true;
		if(val1 && val2) return true;
		if(val1 && val2 == 'true') return true;
		if(val1 == 'true' && val2) return true;
		if(!val1 && val2 == 'false') return true;
		if(val1 == 'false' && !val2) return true;

		// NO?
		return false;
	};

	var _buildRecord = function(datarow, type){
		var out = '';
		out += '<tr dyn:id="' + datarow[options.data.id] + '" dyn:status="' + type + '">';
		if(options.sortable){
			out += '<td class="draghandle toolbars"><img src="' + options.images.draghandle + '"/></td>';
		}
		// Add the columns first.
		for(var x in options.data.columns){
			// The type needs to be set first... it may not have been.
			if(!options.data.columns[x].type) options.data.columns[x].type = 'text';

			out += '<td><div class="viewattributes">';
			out += '<a href="#" class="dyneditlink">';
			if(options.data.columns[x].viewprepend) out += options.data.columns[x].viewprepend;
			out += '<span class="viewattributecontents">';

			switch(options.data.columns[x].type){
				case 'select':
					// Undefined strings get blank.
					if(typeof datarow[options.data.columns[x].key] == 'undefined') out += '';
					// Of course, empty strings get blank.
					else if(datarow[options.data.columns[x].key] == '') out += '';
					// Arrays are a special case... they don't have fancy mapped keys.
					else if(options.data.columns[x].options instanceof Array) out += datarow[options.data.columns[x].key];
					// Objects are the traditional case.
					else out += options.data.columns[x].options[datarow[options.data.columns[x].key]];

					break;
				case 'checkbox':
					out += (_issame(datarow[options.data.columns[x].key], options.data.columns[x].value))? options.data.columns[x].checkedtitle : options.data.columns[x].uncheckedtitle;
					break;
				default:
					out += (typeof datarow[options.data.columns[x].key] != 'undefined')? datarow[options.data.columns[x].key] : '';
			}


			out += '</span>';
			out += '<img src="' + options.images.edit + '" alt="Edit" title="Edit" style="visibility:hidden; float:right; margin-top:-4px;"/>';
			out += '</a></div>';

			out += '<div class="editattributes" style="display:none;">';
			if(options.data.columns[x].editprepend) out += options.data.columns[x].editprepend;

			// Create the actual input object.
			var inname = '';
			inname += options.data.name;
			if(options.data.id) inname += '[' + datarow[options.data.id] + ']';
			inname += '[' + options.data.columns[x].key + ']';

			out += _buildInputField(inname, options.data.columns[x], datarow);

			// These are the 'ok/cancel' buttons.
			out += '<a href="#" class="dynsavelink"><img src="' + options.images.ok + '" alt="ok" title="OK" /></a>';
			out += '<a href="#" class="dyncancellink"><img src="' + options.images.cancel + '" alt="cancel" title="Cancel"/></a>';

			out += '</div>';
			out += '</td>';
		}

		for(var x in options.data.groupradios){
			out += '<td>';
			var inname = options.data.name + '[' + options.data.groupradios[x].key + ']';
			out += '<input type="radio" name="' + inname + '" value="' + datarow[options.data.id] + '"';
			if(datarow[options.data.groupradios[x].key]) out += ' checked="checked"';
			out += '/>';
			if(options.data.groupradios[x].label) out += '<label>' + options.data.groupradios[x].label + '</label>';
			out += '</td>';
		}

		out += '<td class="toolbars">';
		out += '<a href="#" class="dyndeletelink"><img src="' + options.images.del + '" alt="Delete" title="Delete" /></a>';
		out += '</td>';
		out += '</tr>';

		return out;
	};

	// This will bind the necessary events onto elements throughout the editables area.
	var _attachEvents = function(){

		// Delegate is the prefered method of live event watching since jquery 1.4.3+.
		// It's faster and evidently has less bugs than .live().
		target.delegate('td', 'mouseover', function(){
			$(this).find('.viewattributes').find('img').css('visibility', 'visible');
		});

		target.delegate('td', 'mouseout', function(){
			$(this).find('.viewattributes').find('img').css('visibility', 'hidden');
		});

		$(document).on('click', '.' + target_class + ' .dyneditlink', editLinkClickEvent);
		// Ensure that clicking 'save' saves the data and goes back to the view.
		$(document).on('click', '.' + target_class + ' .dynsavelink', saveLinkClickEvent);
		// Ensure that clicking 'cancel' restores the data as it was and goes back to the view.
		$(document).on('click', '.' + target_class + ' .dyncancellink', cancelLinkClickEvent);
		$(document).on('click', '.' + target_class + ' .dynsavenewlink', saveNewLinkClickEvent);
		$(document).on('click', '.' + target_class + ' .dyndeletelink', deleteLinkClickEvent);
		// Ensure that pressing 'enter' on input forms saves and does not submit.
		$(document).on('keypress', '.' + target_class + ' .editattributes input', inputKeyPressEvent);
		$(document).on('keypress', '.' + target_class + ' .createattributes input', newInputKeyPressEvent);

		// And the form submit... ensure everything's been saved up!
		target.closest('form').submit(submitFormEvent);

		// Draggable?
		if(options.sortable) target.tableDnD({dragHandle: 'draghandle'});
	};
}


(function(jQuery) {
	jQuery.extend(jQuery.fn, {
		// jQuery wrapper around the global handler object.
		dyneditable : function(options, data) {

			if (options == undefined) options = {};

			// Run through each element given in by the programmer.
			jQuery(this).each(function(){
				obj = new com_blindacre_dyneditables(options, data);
				obj.bind(this);
				obj.build();
			});
			return this;
		}
	});
})(jQuery);
