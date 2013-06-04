
function EN_Signup(form)
{
	if (form.elements.submitbtn) {
		form.elements.submitbtn.disabled = true;
	}
	
	new Ajax.Form(form, { onComplete: function(t) {
		if (form.elements.submitbtn) {
			form.elements.submitbtn.disabled = false;
		}
		alert(t.responseText);
	} } );
}