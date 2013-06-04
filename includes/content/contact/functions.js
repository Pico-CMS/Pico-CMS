
function ContactForm_Submit(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function(t) {
		var target_url = url('includes/content/contact/view.php?component_id='+component_id);
		new Ajax.Updater('contact_page_'+component_id, target_url, { onComplete: function() {
			document.getElementById('contact_page_'+component_id).scrollIntoView();
		}});
	}});
}