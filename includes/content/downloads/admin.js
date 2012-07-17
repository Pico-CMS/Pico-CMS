
function DL_FileUploaded(filename)
{
	var instance_id = document.getElementById('instance_id').value;
	var target_url = url('includes/content/downloads/submit.php?page_action=add&filename='+urlencode(filename)+'&instance_id='+instance_id);
	new Ajax.Request(target_url, { onComplete: function() {
		// nada
	} } );
}

function DL_DeleteFile(file_id)
{
	if (confirm('Are you sure you want to delete this file?'))
	{
		var instance_id = document.getElementById('instance_id').value;
		var target_url = url('includes/content/downloads/submit.php?page_action=delete&file_id='+file_id+'&instance_id='+instance_id);
		new Ajax.Request(target_url, { onComplete: function() {
			DL_Refresh();
		} } );
	}
}

function DL_MoveFile(file_id, direction)
{
	var instance_id = document.getElementById('instance_id').value;
	var target_url = url('includes/content/downloads/submit.php?page_action=move&file_id='+file_id+'&instance_id='+instance_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		DL_Refresh();
	} } );
}

function DL_Refresh()
{
	if (CKEDITOR.instances.dl_html_desc)
	{
		CKEDITOR.instances.dl_html_desc.destroy();
	}
	var instance_id = document.getElementById('instance_id').value;
	var target_url = url('includes/content/downloads/edit.php?refresh=1&instance_id='+instance_id);
	new Ajax.Updater('file_list', target_url);
}

function DL_FilesUploaded()
{
	DL_Refresh();
}

function DL_Close()
{
	if (CKEDITOR.instances.dl_html_desc)
	{
		CKEDITOR.instances.dl_html_desc.destroy();
	}
	
	var component_id = document.getElementById('component_id').value;
	Pico_ReloadComponent(component_id);
}

function DL_EditDescription(file_id)
{
	var target_url = url('includes/content/downloads/html_description.php?id='+file_id);
	new Ajax.Updater('file_list', target_url, { onComplete: function () {
		// load CK editor
		CKEDITOR.replace('dl_html_desc', { height: 250 });
		document.getElementById('file_upload').style.display = 'none';
		document.getElementById('file_list').style.height = '430px';
	} });
}

function DL_HTML_Submit(form)
{
	// get CK data
	form.elements.html_description.value = CKEDITOR.instances.dl_html_desc.getData();
	CKEDITOR.instances.dl_html_desc.destroy();
	// put in var
	new Ajax.Form(form, { onComplete: function() {
		DL_Refresh();
	} } );
}