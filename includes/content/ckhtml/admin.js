

function CK_Save(close)
{
	if (isNaN(close)) { close = 0; }
	var form = document.getElementById('ck_form');

	form.elements.ck_html.value = CKEDITOR.instances.ck_text.getData();
	var component_id = form.elements.component_id.value;
	var instance_id  = form.elements.instance_id.value;
	new Ajax.Form(form, { onComplete: function() {
		CKT_Reload(component_id, instance_id);

		if (close == 1)
		{
			Pico_CloseAP(func);
		}
	}} );
}

function CKT_Reload(component_id, instance_id)
{
	var func = function() {
		//alert(component_id);
		var id = 'ckhtml_'+component_id;
		if (typeof(CKEDITOR.instances[id]) == 'object') {
			CKEDITOR.instances[id].destroy();
		}
		var edit_url = url('includes/content/ckhtml/submit.php');
		CKEDITOR.inline(id, { toolbar: 'inline', picoSavePath: edit_url, picoComponentId: component_id, picoInstanceId: instance_id });
	}

	Pico_ReloadComponent(component_id);
	
}

function CKT_Close()
{
	if (CKEDITOR.instances.ck_text) { CKEDITOR.instances.ck_text.destroy(); }
}

function CKT_Load()
{
	CKEDITOR.replace('ck_text', { height: 350, on: {
		instanceReady: function (ev) {
			ev.editor.focus();
			if (pico_is_in_edit == 1) {
				Pico_ToggleEditContent();
			}
		}
	}});
}