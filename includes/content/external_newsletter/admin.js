
function EN_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = document.getElementById('component_id').value;
	document.getElementById('signup_complete_text').value = CKEDITOR.instances.ck_signup_complete_text.getData();
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		EN_CloseEdit();
		
		Pico_ReloadComponent(component_id);
		var target_url = url('includes/content/external_newsletter/edit.php?refresh=1&component_id='+component_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			EN_LoadEdit();
		}});
	} } );
}

function EN_SubmitButton(filename)
{
	var obj = document.getElementById('newsletter_submit_button');
	obj.value = filename;
}

function EN_SubmitButtonRollover(filename)
{
	var obj = document.getElementById('newsletter_submit_button_rollover');
	obj.value = filename;
}

function EN_LoadEdit()
{
	CKEDITOR.replace('ck_signup_complete_text', { height: 275 });
}

function EN_CloseEdit()
{
	if (CKEDITOR.instances.ck_signup_complete_text)
	{
		CKEDITOR.instances.ck_signup_complete_text.destroy();
	}
}