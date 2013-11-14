
var Blog2_AutoUpdater;

function Blog2_StopUpdater()
{
	if (typeof(Blog2_AutoUpdater) == 'object')
	{
		Blog2_AutoUpdater.stop();
	}
}

function Blog2_Home()
{
	Blog2_StopUpdater();
	var component_id = document.getElementById('component_id').value;
	
	var obj = document.getElementById('co_main');
	var target_url = url('includes/content/blog2/edit.php?refresh=1&component_id='+component_id);
	
	var func = function() {
		Blog2_Close();
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		}} );
	}
	
	Pico_FadeOut(obj, 100, func);
}

function Blog2_UpdateLastSaves(entry_id)
{
	if (CKEDITOR.instances.last_user_saved_post)
	{
		CKEDITOR.instances.last_user_saved_post.destroy();
	}
	if (CKEDITOR.instances.last_auto_saved_post)
	{
		CKEDITOR.instances.last_auto_saved_post.destroy();
	}
	
	var target_url = url('includes/content/blog2/blog_restore.php?refresh=1&entry_id='+entry_id);
	new Ajax.Updater('blog_restore', target_url, { onComplete: function() {
		CKEDITOR.replace('last_user_saved_post', { height: 275 });
		CKEDITOR.replace('last_auto_saved_post', { height: 275 });
	} });
}

function Blog2_Status(txt)
{
	var obj = document.getElementById('blog_status');
	obj.innerHTML = txt;

	var func = function() {
		obj.innerHTML = '';
	}

	setTimeout(func, 3000);
}


function Blog2_Save(publish)
{
	var form = document.getElementById('blog_content_form');
	var component_id = form.elements.component_id.value;
	form.elements.blog_entry_text.value = CKEDITOR.instances.blog_story.getData();

	if (publish == 1) { form.elements.published.checked = true; }

	new Ajax.Form(form, { onComplete: function(t) {
		Pico_ReloadComponent(component_id);

		if (publish == 1) 
		{ 
			Pico_CloseAP();
		}
		else 
		{
			Blog2_Status(t.responseText);
			Blog2_UpdateLastSaves(form.elements.entry_id.value);
		}
	} } );
}

function Blog2_Publish()
{
	var form = document.getElementById('blog_content_form');
	form.elements.page_action.value = 'publish';
	Blog2_Save();
}

function Blog2_AutoSave()
{
	var form = document.getElementById('blog_content_form');
	if (form)
	{
		var f = document.createElement("form");
		f.setAttribute('method',"post");
		f.setAttribute('action',url('includes/content/blog2/submit.php'));

		var i = document.createElement("input"); //input element, text
		i.setAttribute('type',"hidden");
		i.setAttribute('name',"page_action");
		i.value = 'draft';
		f.appendChild(i);

		var i = document.createElement("input"); //input element, text
		i.setAttribute('type',"hidden");
		i.setAttribute('name',"blog_entry_text");
		i.value = CKEDITOR.instances.blog_story.getData();
		f.appendChild(i);

		var i = document.createElement("input"); //input element, text
		i.setAttribute('type',"hidden");
		i.setAttribute('name',"entry_id");
		i.value = form.elements.entry_id.value
		f.appendChild(i);

		new Ajax.Form(f, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				Blog2_Status(t.responseText);
				Blog2_UpdateLastSaves(form.elements.entry_id.value);
			}
		}});
	}
}

function Blog2_Close()
{
	if (CKEDITOR.instances.blog_story)
	{
		CKEDITOR.instances.blog_story.destroy();
	}
	if (CKEDITOR.instances.last_user_saved_post)
	{
		CKEDITOR.instances.last_user_saved_post.destroy();
	}
	if (CKEDITOR.instances.last_auto_saved_post)
	{
		CKEDITOR.instances.last_auto_saved_post.destroy();
	}

	$('component_options').setStyle({'display' : 'block'});
	Blog2_StopUpdater();
}

function Blog2_NewStory(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		var response = t.responseText;
		
		var result = response.split('|');
		if (result[0] == 0)
		{
			// success, edit the story
			var entry_id = result[1];
			Blog2_EditStory(entry_id);
		}
		else
		{
			// something bad
			var error = result[1];
			alert(error);
		}
	} });
}

function Blog2_TabActivate(obj, container)
{
	$$('div.tabbed_content').each(function(el) {
		el.setStyle({'display' : 'none'});
	});

	$(container).setStyle({'display' : 'block'});

	$$('li.blog_sidebar_item').each(function(el) {
		el.removeClassName('active');
	});

	$(obj).addClassName('active');

	/*
	var fields = getElementsByClassName('tabbed_content', '*');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	document.getElementById(container).style.display = 'block';
	*/
}

function Blog2_EditStory(entry_id)
{
	var obj = document.getElementById('co_main');
	var target_url = url('includes/content/blog2/blog_entry.php?id='+entry_id);
	
	var func = function() {
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			$('component_options').setStyle({'display' : 'none'});
			Pico_FadeIn(obj, 0);

			CKEDITOR.replace('blog_story', { height: 350 });
			CKEDITOR.replace('last_user_saved_post', { height: 275 });
			CKEDITOR.replace('last_auto_saved_post', { height: 275 });

			var func = function() {
				Blog2_AutoSave();
			}
			Blog2_AutoUpdater = new PeriodicalExecuter(func, 30);
		}} );
	}
	
	Pico_FadeOut(obj, 100, func);
}

function Blog2_DeleteEntry(entry_id)
{
	if (confirm('Are you sure you want to delete this entry?'))
	{
		var target_url = url('includes/content/blog2/submit.php?page_action=delete_entry&entry_id='+entry_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Blog2_Home();
		} } );
	}
}

function Blog2_RenameCategory(component_id)
{
	var category_id = $('rename_category_id').value;
	var new_name = urlencode($('rename_category').value);

	var target_url = url('includes/content/blog2/submit.php?page_action=edit_category&category_id='+category_id+'&category='+new_name);
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0) {
			alert(t.responseText);
		}
		else {
			Blog2_ChooseCategories(component_id);
		}
	}});
}

function Blog2_DeleteCategory(component_id)
{
	if (confirm('Are you sure you want to delete this category?'))
	{
		var category_id = $('delete_category_id').value;

		var target_url = url('includes/content/blog2/submit.php?page_action=delete_category&category_id='+category_id);
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0) {
				alert(t.responseText);
			}
			else {
				Blog2_ChooseCategories(component_id);
			}
		}});
	}
}

function Blog2_ReloadCategory(component_id, selected)
{
	selected = (typeof selected == "undefined") ? '' : selected;
	
	// blog_category
	var target_url = url('includes/content/blog2/submit.php?page_action=reload_category&component_id='+component_id);
	new Ajax.Updater('blog_category', target_url, { onComplete: function() {
		if (selected.length > 0)
		{
			// select the category we are passing in
			var obj = document.getElementById('blog_cat_'+component_id);
			
			var size = obj.options.length;
			for (x=0; x<size; x++)
			{
				var text = obj.options[x].text;
				if (text == selected)
				{
					obj.selectedIndex = x;
					return;
				}
			}
		}
	} });
}

function Blog2_UpdateOptions(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function() {
		Pico_ReloadComponent(component_id);
		alert('Settings Updated');
	} } );
}

function Blog2_ImageUploaded(filename)
{
	//alert(filename);
	var entry_id = document.getElementById('blog_entry_id').value;
	var target_url = url('includes/content/blog2/submit.php?page_action=blog_image&entry_id='+entry_id+'&filename='+urlencode(filename));
	
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			// error
			alert(t.responseText);
		}
		else
		{
			var obj = document.getElementById('blog_image');
			var target_url = url('includes/content/blog2/blog_image.php?refresh=1&entry_id='+entry_id);
			
			var func = function() {
				new Ajax.Updater('blog_image', target_url, { onComplete: function() {
					Pico_FadeIn(obj, 0);
				}} );
			}
			
			Pico_FadeOut(obj, 100, func);
		}
	} } );
}

function Blog2_DeleteImage()
{
	var entry_id = document.getElementById('blog_entry_id').value;
	if (confirm('Are you sure you want to remove this image from your post?'))
	{
		var target_url = url('includes/content/blog2/submit.php?page_action=delete_blog_image&entry_id='+entry_id);
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				// error
				alert(t.responseText);
			}
			else
			{
				var obj = document.getElementById('blog_image');
				var target_url = url('includes/content/blog2/blog_image.php?refresh=1&entry_id='+entry_id);
				
				var func = function() {
					new Ajax.Updater('blog_image', target_url, { onComplete: function() {
						Pico_FadeIn(obj, 0);
					}} );
				}
				
				Pico_FadeOut(obj, 100, func);
			}
		} } );
	}
}

function Blog2_DeleteComment(entry_id, comment_id)
{
	if (confirm('Are you sure you want to delete this comment?'))
	{
		var target_url = url('includes/content/blog2/submit.php?page_action=delete_comment&comment_id='+comment_id);
		new Ajax.Request(target_url, { onComplete: function() {
			var obj = document.getElementById('blog_admin_comments');
			
			var func = function() {
				var target_url = url('includes/content/blog2/blog_comments.php?refresh=1&entry_id='+entry_id);
				new Ajax.Updater('blog_admin_comments', target_url, { onComplete: function() {
					Pico_FadeIn(obj, 0);
				}} );
			}
			Pico_FadeOut(obj, 100, func);
		} } );
	}
}

function Blog2_ApproveComment(entry_id, comment_id)
{
	var target_url = url('includes/content/blog2/submit.php?page_action=approve_comment&comment_id='+comment_id);
	new Ajax.Request(target_url, { onComplete: function() {
		var obj = document.getElementById('blog_admin_comments');
		
		var func = function() {
			var target_url = url('includes/content/blog2/blog_comments.php?refresh=1&entry_id='+entry_id);
			new Ajax.Updater('blog_admin_comments', target_url, { onComplete: function() {
				Pico_FadeIn(obj, 0);
			}} );
		}
		Pico_FadeOut(obj, 100, func);
	} } );
}

function Blog2_LoadDefaultLayout(field_name)
{
	var field = document.getElementById(field_name);
	
	var cont = false;
	
	if (field.value.length > 0)
	{
		if (confirm('Loading a default layout will overwrite your current settings, continue?'))
		{
			cont = true;
		}
	}
	else
	{
		cont = true;
	}
	
	if (cont == true)
	{
		var target_url = url('includes/content/blog2/submit.php?page_action=load_layout&layout='+field_name);
		new Ajax.Request(target_url, { onComplete: function(t) {
			field.value =  t.responseText;
		}} );
	}
}

function Blog2_Publish(obj)
{
	document.getElementById('publish1').checked = obj.checked;
	document.getElementById('publish2').checked = obj.checked;
}

function Blog2_inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

function Blog2_GetTags()
{
	var tag_text = $('blog_tags').getValue();
	var new_tags = new Array();
	tags = tag_text.split(',');

	for (var x = 0; x < tags.length; x++)
	{
		var tag = tags[x];
		tag = tag.replace(/^\s+|\s+$/g,''); // trims it
		//tags[x] = tag;
		if (tag.length > 0)
		{
			new_tags.push(tag);
		}
	}

	return new_tags;
}

function Blog2_RemoveTag(remove_tag)
{
	var tags = Blog2_GetTags();
	var new_tags = new Array();

	for (var x = 0; x < tags.length; x++)
	{
		var tag = tags[x];
		if (tag != remove_tag)
		{
			if (tag.length > 0)
			{
				new_tags.push(tag);
			}
		}
	}

	return new_tags;
}

function Blog2_UpdateTags()
{
	$('blog2_tagbox').setStyle({'display':'block'});
	
	var tags = Blog2_GetTags();

	$$('div.tag_entry').each(function(el) 
	{
		var tag = el.getAttribute('tag_name');
		if (Blog2_inArray(tag, tags)) 
		{
			$(el).addClassName('active');   
		}
		else
		{
			$(el).removeClassName('active');   
		}

		if ($(el).getAttribute('click_setup') != '1')
		{
			$(el).observe('click', function() {
				if ($(el).hasClassName('active') == false)
				{
					var tag  = ($(this).getAttribute('tag_name'));
					var tags = Blog2_GetTags();
					tags.push(tag);
					$('blog_tags').setValue(tags.join(', '));
					$(this).addClassName('active');
					Blog2_UpdateTags();
				}
				else
				{
					var tag  = ($(this).getAttribute('tag_name'));
					var tags = Blog2_RemoveTag(tag);
					$('blog_tags').setValue(tags.join(', '));
					$(this).removeClassName('active');
					Blog2_UpdateTags();
				}
			});

			$(el).setAttribute('click_setup', 1);
		}
	});
}

function BLog2_CloseTags()
{
	$('blog2_tagbox').setStyle({'display':'none'});
}

function Blog2_RestoreEntry(field)
{
	if (confirm('Are you sure you want to replace your current draft with this saved draft?'))
	{
		Blog2_TabActivate($('bes_1'), 'blog_content');
		var saved_data = CKEDITOR.instances[field].getData();
		CKEDITOR.instances.blog_story.setData(saved_data);
	}
}

function Blog2_Suggest(result)
{
	var entry_id = document.getElementById('blog_entry_id').value;
	var target_url = url('includes/content/blog2/submit.php?page_action=suggest&entry_id='+entry_id+'&result='+result);

	new Ajax.Request(target_url, { onComplete: function(t) {
		var response = parseInt(t.responseText);
		if (!isNaN(response))
		{
			var form = document.getElementById('blog_content_form');
			form.elements['related'+result].value = response;
		}
		else
		{
			alert('Unable to find a related post');
		}
	}});
}


function Blog2_Preview()
{
	var form = document.getElementById('blog_content_form');
	if (form)
	{
		form.elements.blog_entry_text.value = CKEDITOR.instances.blog_story.getData();
		form.elements.page_action.value = 'preview';

		new Ajax.Form(form, { onComplete: function(t) {
			form.elements.page_action.value = 'edit_story';
			
			target_url = url(CURRENT_ALIAS + '/blog-preview');
			window.open(target_url, '_blank');
		}});
	}
}

function Blog2_Filter(obj)
{
	var search = obj.value.toLowerCase();
	var counter = 0;

	$$('table.blog_entry_list tr').each(function(el) {
		var title = el.getAttribute('searchtitle');
		var compare = title.toLowerCase();

		if (compare.indexOf(search) != -1)
		{
			var cls = (counter % 2 == 0) ? 'a' : 'b';
			el.removeClassName('a');
			el.removeClassName('b');
			el.addClassName(cls);
			counter = counter + 1;
			el.setStyle({ display: 'table-row' });
		}
		else
		{
			el.setStyle({ display: 'none' });
		}
	});
}

function Blog2_InsertAuthor(obj)
{
	var value = obj.value;
	if (value.length > 0)
	{
		$('blog_author').value = obj.value;
		obj.value = '';
	}
}

function Blog2_ChooseCategories(component_id)
{
	$('blog_category_box').innerHTML = 'Loading...';
	var target_url = url('includes/content/blog2/submit.php?page_action=get_categories&component_id='+component_id);
	new Ajax.Updater('blog_category_box', target_url, {onComplete: function () {
		$('blog_category_box').setStyle({ display: 'block'});

		Blog2_HighlightCategories();
	}});
}

function Blog2_CloseCategories()
{
	$('blog_category_box').setStyle({ display: 'none'});
}

function Blog2_AddNewCategory(component_id)
{
	var category = $('new_category').value;
	var target_url = url('includes/content/blog2/submit.php?page_action=add_category&component_id='+component_id+'&category='+urlencode(category));
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			Blog2_ChooseCategories(component_id);
		}
	}});
}

function Blog2_HighlightCategories()
{
	var current_categories = $('blog_entry_categories').value;
	var cat_array = current_categories.split(',');
	var cat_names = new Array;

	$$('#blog_category_box div.entry').each(function(el) {
		var this_id = el.getAttribute('category_id');
		if (Blog2_inArray(this_id, cat_array))
		{
			el.addClassName('active');
			cat_names.push($(el).innerHTML);
		}
		else
		{
			el.removeClassName('active');
		}
	});

	$('blog_category_list').innerHTML = (cat_names.length > 0) ? cat_names.join(', ') : 'None selected';
}

function Blog2_SelectCategory(obj)
{
	var category_id        = $(obj).getAttribute('category_id');
	var current_categories = $('blog_entry_categories').value;
	var cat_array          = (current_categories.length > 0) ? current_categories.split(',') : new Array;

	if (Blog2_inArray(category_id, cat_array))
	{
		// remove it
		var new_array = new Array;
		while (id = cat_array.pop())
		{
			if ((!isNaN(id)) && (id != category_id)) 
			{
				new_array.push(id);
			}
		}
		cat_array = new_array;
	}
	else
	{
		// add it
		cat_array.push(category_id);
	}

	$('blog_entry_categories').value = cat_array.join(',');
	Blog2_HighlightCategories();
}

function Blog2_LoadRename(obj)
{
	var val = obj.value;
	var txt = obj.options[obj.selectedIndex].text;
	$('rename_category').value = txt;
}