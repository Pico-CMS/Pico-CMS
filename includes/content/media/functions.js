
function MG_ShowGridImage(image_id)
{
	var fields = getElementsByClassName('grid_image', '*');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	
	var obj = document.getElementById('grid'+image_id);
	obj.style.display = 'block';
}

function MG_ShowProjectCategory(component_id, category_id, image_count)
{
	if (image_count == null)
	{
		image_count = 0;
	}
	var container = 'gallery_description_' + component_id;
	var target_url = url('includes/content/media/galleries/project/show_category.php?category='+category_id+'&component_id='+component_id+'&image_count='+image_count+'&alias='+urlencode(CURRENT_ALIAS));
	new Ajax.Updater(container, target_url);
}

function MG_Active(obj)
{
	var fields = getElementsByClassName('click catlist');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.firstChild.className = '';
	}
	obj.className = 'active';
}

function JScriptG_ShowImage(image_id, component_id, click)
{
	var click = (click != null) ? click : 0;
	
	if (click == 1)
	{
		var obj = document.getElementById('jscript_gallery_'+component_id);
		obj.doRotate = 0; // no more rotating
	}
	
	var fields = getElementsByClassName('jscript_image_'+component_id, '*');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	
	var obj = document.getElementById('jscript_'+image_id);
	
	// highlight the current thumbnail
	

	// reset all thumbnails for this component to regular
	var fields = getElementsByClassName('jscript_thumbnail_'+component_id, '*');
	for (var x = 0; x < fields.length; x++)
	{
		var el = fields[x];
		el.className = 'thumbnail jscript_thumbnail_'+component_id;
	}
	var thumbObj = document.getElementById('jscript_thumb_'+image_id);
	if (thumbObj != null)
	{
		thumbObj.className += ' active';
	}
	
	
	set_opacity(obj, 0);
	obj.style.display = 'block';
	JScriptG_FadeIn(obj, 0);
}

function JScriptG_FadeIn(obj, alpha, complete_function)
{
	var new_alpha = alpha + 5;
	if (new_alpha > 100) { new_alpha = 100; }
	set_opacity(obj, new_alpha);
	
	if (new_alpha != 100)
	{
		var func = function() { JScriptG_FadeIn(obj, new_alpha, complete_function); };
		setTimeout(func, 25);
	}
	else
	{
		if (complete_function != null)
		{
			complete_function();
		}
	}
}

function JScriptG_ShowThumbNext(component_id)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	var page_number = parseInt(obj.pageNumber);
	if (isNaN(page_number)) { page_number = 0; }
	var num = page_number+1;
	
	var new_obj = document.getElementById('jscript_thumbrow_'+num);
	if (new_obj != null)
	{
		obj.pageNumber = num;
		
		var fields = getElementsByClassName('jscript_thumbrow_'+component_id, '*');
		for (x=0; x<fields.length; x++)
		{
			var el = fields[x];
			el.style.display = 'none';
		}
	
		set_opacity(new_obj, 0);
		new_obj.style.display = 'block';
		JScriptG_FadeIn(new_obj, 0);
	}
}


function JScriptG_ShowThumbPrevious(component_id)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	var page_number = parseInt(obj.pageNumber);
	if (isNaN(page_number)) { page_number = 0; }
	var num = page_number-1;
	
	var new_obj = document.getElementById('jscript_thumbrow_'+num);
	if (new_obj != null)
	{
		obj.pageNumber = num;
		
		var fields = getElementsByClassName('jscript_thumbrow_'+component_id, '*');
		for (x=0; x<fields.length; x++)
		{
			var el = fields[x];
			el.style.display = 'none';
		}
	
		set_opacity(new_obj, 0);
		new_obj.style.display = 'block';
		JScriptG_FadeIn(new_obj, 0);
	}
}

function JscriptG_RotateImage(component_id)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	if (obj.doRotate == 1)
	{
		var imageIndex = obj.imageNumber;
		imageIndex = imageIndex + 1;
		
		//alert(imageIndex + '/' + 'jscript_'+component_id+'_'+imageIndex);
		
		var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
		if (imageContainer == null)
		{
			// wrap to the first
			imageIndex = 0;
			var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
		}
		
		obj.imageNumber = imageIndex;
		
		var fade_id = imageContainer.value;
		JScriptG_ShowImage(fade_id, component_id);
		
		var func = function() {
			JscriptG_RotateImage(component_id);
		}
		
		setTimeout(func, obj.delaySpeed);
	}
}


var func = function()
{
	var fields = getElementsByClassName('jscript_gallery_locator', '*');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		//alert(el.name);
		if (el.name == 'component_id')
		{
			var component_id = el.value;
			var rotate = document.getElementById('jscript_rotate_'+component_id).value;
			var speed = document.getElementById('jscript_speed_'+component_id).value;
			var num_images = document.getElementById('jscript_num_images_'+component_id).value;
			
			JscriptG_StartRotate(component_id, rotate, speed, num_images);
		}
	}
}

add_load_event(func);

var JscriptG_DoRotateImage = 1;

//

function JscriptG_ShowPrevImage(component_id)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	obj.doRotate = 0; // no more rotating
	var imageIndex = parseInt(obj.imageNumber);
	imageIndex = imageIndex - 1;
	
	/*
	if (isNaN(imageIndex))
	{
		imageIndex = 0;
	}*/
	
	var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
	
	if (imageContainer == null)
	{
		var numImages = parseInt(obj.numImages);
		if (isNaN(numImages))
		{
			var numImages = parseInt(document.getElementById('jscript_num_images_'+component_id).value);
			//alert('in/'+numImages);
		}
		
		imageIndex = (numImages - 1);
		var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
	}
	
	if (imageContainer != null)
	{
		obj.imageNumber = imageIndex;
		var fade_id = imageContainer.value;
		JScriptG_ShowImage(fade_id, component_id);
	}
}

function JscriptG_ShowNextImage(component_id)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	obj.doRotate = 0; // no more rotating
	var imageIndex = parseInt(obj.imageNumber);
	
	if (isNaN(imageIndex)) // this is here in case someone goes click happy before the page loads
	{
		imageIndex = 0;
	}
	
	imageIndex = imageIndex + 1;
	
	var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
	if (imageContainer == null)
	{
		imageIndex = 0;
		var imageContainer = document.getElementById('jscript_image_id_'+component_id+'_'+imageIndex);
	}
	
	if (imageContainer != null)
	{
		obj.imageNumber = imageIndex;
		var fade_id = imageContainer.value;
		JScriptG_ShowImage(fade_id, component_id);
	}
}

function JscriptG_StartRotate(component_id, rotate, delay, num_images)
{
	var obj = document.getElementById('jscript_gallery_'+component_id);
	obj.pageNumber = 0;
	obj.numImages = num_images;
	
	if (isNaN(obj.imageNumber)) // only do if this hasn't been done, could happen if someone clicks before the page finishes loading
	{
		if (isNaN(obj.doRotate))
		{
			obj.doRotate = rotate;
		}
		
		obj.imageNumber = 0;
		obj.delaySpeed = delay;
	}
	
	var func = function() {
		JscriptG_RotateImage(component_id);
	}
	setTimeout(func, delay);
}