
function EN_Signup(form)
{
	if (form.elements.submitbtn) {
		form.elements.submitbtn.disabled = true;
	}
	
	new Ajax.Form(form, { onComplete: function(t) {
		if (form.elements.submitbtn) {
			form.elements.submitbtn.disabled = false;
		}
		//alert(t.responseText);

		console.log(t.responseText);

		EN_CenterWindow('en_splash');

		$('en_splash_content').update(t.responseText);
		$('en_splash_bg').setStyle({'display':'block'});

		EN_CenterWindow('en_splash');
	} } );
}

function EN_CenterWindow(win)
{
	var obj = document.getElementById(win);
	if (obj)
	{
		var window_size = WindowSize();
		
		var obj_width  = parseFloat(obj.offsetWidth);
		var obj_height = parseFloat(obj.offsetHeight);
		
		var left = (window_size.width - obj_width) / 2;
		var top  = (window_size.height - obj_height) / 2;
		
		obj.style.top  = top + 'px';
		obj.style.left = left + 'px';
	}
}

function EN_ClosePopup()
{
	$('en_splash_bg').setStyle({'display':'none'});
}