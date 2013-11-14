
function FAQ_Delete(faq_id)
{
	if (confirm('Are you sure you want to delete this topic?'))
	{
		var instance_id  = document.getElementById('instance_id').value;
		
		var target_url = url('includes/content/faq/submit.php?page_action=delete&faq_id='+faq_id);
		new Ajax.Request(target_url, { onComplete: function() {
			FAQ_Reload(instance_id)
		} });
	}
}

function FAQ_Move(faq_id, direction)
{
	var target_url = url('includes/content/faq/submit.php?page_action=move&direction='+direction+'&faq_id='+faq_id);
	new Ajax.Request(target_url, { onComplete: function() {
		var instance_id  = document.getElementById('instance_id').value;
		var target_url = url('includes/content/faq/edit.php?reload=1&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			// instant update
		} });
	} });
}

function FAQ_Edit(faq_id)
{
	var obj = document.getElementById('co_main');
	var instance_id  = document.getElementById('instance_id').value;
	
	var func = function() {
		var target_url = url('includes/content/faq/edit.php?reload=1&edit='+faq_id+'&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			FAQ_SetupCK();
			Pico_FadeIn(obj, 0);
		} });
	}
	
	Pico_FadeOut(obj, 100, func);
}

function FAQ_Add()
{
	var obj = document.getElementById('co_main');
	var instance_id  = document.getElementById('instance_id').value;
	
	var func = function() {
		var target_url = url('includes/content/faq/edit.php?reload=1&add=1&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			FAQ_SetupCK();
			Pico_FadeIn(obj, 0);
		} });
	}
	
	Pico_FadeOut(obj, 100, func);
}

function FAQ_Reload(instance_id)
{
	var obj = document.getElementById('co_main');
	
	var func = function() {
		FAQ_CleanCK();
		var target_url = url('includes/content/faq/edit.php?reload=1&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		} });
	}

	Pico_ReloadInstance(instance_id);
	Pico_FadeOut(obj, 100, func);
}

function FAQ_Submit(form)
{
	form.elements.submitbtn.disabled = true;
	var instance_id  = form.elements.instance_id.value;

	form.elements.answer.value = CKEDITOR.instances.faq_answer.getData();

	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		FAQ_Reload(instance_id);
	}} );
}

function FAQ_SetupCK()
{
	CKEDITOR.replace('faq_answer');
}

function FAQ_CleanCK()
{
	if (CKEDITOR.instances.faq_answer) {
		CKEDITOR.instances.faq_answer.destroy();
	}
}