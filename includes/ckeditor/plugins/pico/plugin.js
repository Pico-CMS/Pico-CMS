CKEDITOR.plugins.add('pico',
{
	init: function(editor)
	{
		//Plugin logic goes here.
		editor.addCommand('enterFullEditor',
		{
			exec : function(editor)
			{
				Pico_EditContentId(editor.config.picoComponentId, REQUEST_URI, CURRENT_PAGE);
			}
		});

		editor.addCommand('saveToPico',
		{
			exec : function(editor)
			{
				var form = document.createElement('form');
				//form.setAttribute('id', 'save_form');
				form.setAttribute('method', 'post');
				form.action = editor.config.picoSavePath;
				
				var field = document.createElement('input');
				field.setAttribute('type', 'hidden');
				field.setAttribute('name', 'component_id');
				field.setAttribute('value', editor.config.picoComponentId);
				form.appendChild(field);

				var field = document.createElement('input');
				field.setAttribute('type', 'hidden');
				field.setAttribute('name', 'instance_id');
				field.setAttribute('value', editor.config.picoInstanceId);
				form.appendChild(field);

				var field = document.createElement('input');
				field.setAttribute('type', 'hidden');
				field.setAttribute('name', 'ck_html');
				field.setAttribute('value', editor.getData());
				form.appendChild(field);

				new Ajax.Form(form, { onComplete: function() {
					alert('Your changes have been saved.');
				}});
				
				//editor.ui.items.PicoSave.icon = this.path + 'icons/loading.gif';
			}
		});

		editor.ui.addButton('Pico',
		{
			label: 'Enter Full Editor',
			command: 'enterFullEditor',
			icon: this.path + 'icons/pico.png'
		} );

		editor.ui.addButton('PicoSave',
		{
			label: 'Save',
			command: 'saveToPico',
			icon: this.path + 'icons/save.png'
		} );
	}
} );