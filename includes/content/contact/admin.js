
function CF_UpdateSettings(form)
{
	var component_id = form.elements.component_id.value;
	
	form.elements.complete_message.value = CKEDITOR.instances.complete_message_editor.getData();
	form.elements.preview_message.value = CKEDITOR.instances.preview_message_editor.getData();
	form.elements.interim_message.value = CKEDITOR.instances.interim_message_editor.getData();
	CF_CloseForm();
	
	new Ajax.Form(form, { onComplete: function() {
		
		Pico_ReloadComponent(component_id);
		alert('Settings Saved');
		var target_url = url('includes/content/contact/edit.php?reload=1&component_id='+component_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			CF_LoadForm();
		} } );
		//Pico_CloseAP(func);
	} } );
}

function CF_ReloadFieldArea(component_id)
{
	var obj = document.getElementById('cf_fields');

	var func = function() {
		update_url = url('includes/content/contact/submit.php?full=yes&page_action=update_fields&component_id='+component_id);
		new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		} });

	}
	Pico_FadeOut(obj, 100, func);
}

function CF_AddField(form)
{
	var component_id = form.elements.component_id.value;
	var obj = document.getElementById('cf_fields');
	var parent_id = form.elements.parent_id.value;
	
	var func = function() {
		new Ajax.Form(form, { onComplete: function() {
			Pico_ReloadComponent(component_id);

			if (parent_id != -1) {
				var update_url = url('includes/content/contact/submit.php?full=yes&page_action=edit_group_fields&component_id='+component_id+'&field_id='+parent_id);
			}
			else {
				var update_url = url('includes/content/contact/submit.php?full=yes&page_action=update_fields&component_id='+component_id);
			}
			
			new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
				Pico_FadeIn(obj, 0);
			} });
		} } );
	}
	Pico_FadeOut(obj, 100, func);
}

function CF_EditField(component_id, field_id, parent_id)
{
	var update_url = url('includes/content/contact/submit.php?page_action=edit_field&parent_id='+parent_id+'&field_id='+field_id+'&component_id='+component_id);
	new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
		Pico_ReloadComponent(component_id);
	}});
}

function CF_DeleteField(component_id, field_id, parent_id)
{
	var obj = document.getElementById('cf_fields');
	if (confirm('Are you sure you want to delete this field'))
	{
		var target_url = url('includes/content/contact/submit.php?page_action=delete_field&parent_id='+parent_id+'&field_id='+field_id+'&component_id='+component_id);
		new Ajax.Request(target_url, { onComplete: function() {

			if (parent_id != -1) {
				var update_url = url('includes/content/contact/submit.php?full=no&page_action=edit_group_fields&component_id='+component_id+'&field_id='+parent_id);
			}
			else {
				var update_url = url('includes/content/contact/submit.php?full=no&page_action=update_fields&component_id='+component_id);
			}

			new Ajax.Updater('cf_fieldlist', update_url, { onComplete: function() {
				Pico_ReloadComponent(component_id);

				// reload the form if we just deleted a field we were editing
				var form = document.getElementById('cf_field_form');
				if ((form.elements.page_action.value == 'edit_field_post') && (field_id == form.elements.field_id.value) ){
					CF_ReloadFieldArea(component_id);
				}
			} });
		} });
	}
}

function CF_MoveField(component_id, field_id, parent_id, direction)
{
	var obj = document.getElementById('cf_fields');

	var target_url = url('includes/content/contact/submit.php?page_action=move_field&parent_id='+parent_id+'&field_id='+field_id+'&component_id='+component_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		if (parent_id != -1) {
			var update_url = url('includes/content/contact/submit.php?full=no&page_action=edit_group_fields&component_id='+component_id+'&field_id='+parent_id);
		}
		else {
			var update_url = url('includes/content/contact/submit.php?full=no&page_action=update_fields&component_id='+component_id);
		}

		new Ajax.Updater('cf_fieldlist', update_url, { onComplete: function() {
			Pico_ReloadComponent(component_id);
		} });
	} });
}

function CF_Toggle(container)
{
	var obj = document.getElementById(container);
	if (obj.style.display == 'none')
	{
		obj.style.display = 'block';
	}
	else
	{
		obj.style.display = 'none';
	}
}

function CF_SetDownloadFile(filename)
{
	alert(filename + ' uploaded');
}

function CF_DeleteAllFiles(component_id)
{
	if (confirm('Are you sure you want to delete files associated with this form? Doing so will result in this form being a standard contact form until a new file is uploaded'))
	{
		var target_url = url('includes/content/contact/submit.php?page_action=clear_download&component_id='+component_id);
		new Ajax.Request(target_url, { onComplete: function() {
			alert('File removed');
		} } );
	}
}

function CF_SubmitButton(filename)
{
	var obj = document.getElementById('contact_submit_button');
	obj.value = filename;
}

function CF_DeleteHistory(component_id)
{
	if (confirm('Are you sure you want to delete the contact form history? This cannot be undone'))
	{
		var target_url = url('includes/content/contact/submit.php?page_action=clear_history&component_id='+component_id);
		new Ajax.Request(target_url, { onComplete: function() {
			var target_url = url('includes/content/contact/history.php?reload=1&component_id='+component_id);
			new Ajax.Updater('cf_history', target_url);
		} } );
	}
}

function CF_LoadDirectoryFields(component_id, dir_component_id)
{
	if (dir_component_id.length > 0)
	{
		var target_url = url('includes/content/contact/submit.php?page_action=get_directory_fields&component_id='+component_id+'&dir_component_id='+dir_component_id);
		new Ajax.Updater('cf-directory-field', target_url);
	}
	else
	{
		$('cf-directory-field').innerHTML = '';
	}
}

function CF_LoadForm()
{
	CKEDITOR.replace('complete_message_editor', { height: 275 });
	CKEDITOR.replace('preview_message_editor', { height: 275 });
	CKEDITOR.replace('interim_message_editor', { height: 275 });
}

function CF_CloseForm()
{
	if (CKEDITOR.instances.complete_message_editor)
	{
		CKEDITOR.instances.complete_message_editor.destroy();
	}
	if (CKEDITOR.instances.preview_message_editor)
	{
		CKEDITOR.instances.preview_message_editor.destroy();
	}
	if (CKEDITOR.instances.interim_message_editor)
	{
		CKEDITOR.instances.interim_message_editor.destroy();
	}
}

function CF_SubmitButton(filename)
{
	var typeobj = document.getElementById('cf_button_type');
	var button_type  = typeobj.value;
	var component_id = document.getElementById('component_id').value;

	var target_url = url('includes/content/contact/submit.php?page_action=preview_button&component_id='+component_id+'&filename='+urlencode(filename));
	new Ajax.Request(target_url, { onComplete: function(t) {
		var response = t.responseText;
		var info = response.split('|');
		if (info[0] == '0') {
			alert(info[1])
		}
		else
		{
			document.getElementById('preview_'+button_type).innerHTML = info[1];
			var obj = document.getElementById('cfbutton_'+button_type);
			obj.value = filename;
		}
	}});
}

function CF_CheckForPattern(obj)
{
	var fieldtype = obj.value;
	if (obj.value.length == 0) {
		$('cf_pattern').setStyle({
			'display' : 'none'
		});
		$('cf_options_fields').setStyle({
			'display' : 'none'
		});
		$$('tr.dir_dropdown').each(function (el) {
			el.setStyle({
				'display' : 'none'
			});
		});
		return;
	}

	if (fieldtype == 'dir_dropdown') {
		$$('tr.dir_dropdown').each(function (el) {
			el.setStyle({
				'display' : 'table-row'
			});
		});
	}
	else
	{
		$$('tr.dir_dropdown').each(function (el) {
			el.setStyle({
				'display' : 'none'
			});
		});
	}

	// see if we need to display pattern
	var obj = document.getElementById('cf_'+fieldtype+'_use_pattern');
	if (obj.value == 1)
	{
		$('cf_pattern').setStyle({
			'display' : 'table-row'
		});
	}
	else
	{
		$('cf_pattern').setStyle({
			'display' : 'none'
		});
	}

	$$('div.cf_op').each(function (el) {
		$(el).setStyle({
			'display' : 'none'
		});
	});

	// see if we need to display options
	var obj = document.getElementById('cf_'+fieldtype+'_use_cfop');
	if (obj.value == 1)
	{
		$('cf_options_fields').setStyle({
			'display' : 'table-row'
		});	

		$('cfop_'+fieldtype).setStyle({
			'display' : 'table-row'
		});
	}
	else
	{
		$('cf_options_fields').setStyle({
			'display' : 'none'
		});
	}
}

function CF_EditGroupFields(component_id, field_id)
{
	var update_url = url('includes/content/contact/submit.php?page_action=edit_group_fields&field_id='+field_id+'&component_id='+component_id);
	new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
		
	} });
}
function CF_EditGroupBack(component_id)
{
	var update_url = url('includes/content/contact/submit.php?full=yes&page_action=update_fields&component_id='+component_id);
	new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
	} });
}

function CF_CopyGroup(component_id, field_id)
{
	var new_name = prompt('Enter a name for this group');
	if (new_name != null) {
		var target_url = url('includes/content/contact/submit.php?page_action=copy_group&field_id='+field_id+'&component_id='+component_id+'&new_name='+urlencode(new_name));
		new Ajax.Request(target_url, { onComplete: function() {
			var update_url = url('includes/content/contact/submit.php?full=yes&page_action=update_fields&component_id='+component_id);
			new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
			} });
		}});
	}
}