
function Blog_ShowCommentForm(obj, entry_id, comment_id)
{
	comment_id = (comment_id == null) ? 0 : comment_id;
	
	var obj_id = 'blog_comment_' + entry_id + '_' + comment_id;
	
	if (document.getElementById(obj_id) == null)
	{
		commentForm = document.createElement('div');
		commentForm.id = obj_id;
		commentForm.className = 'blog_comment_window';
		commentForm.innerHTML = 'Loading comment form...';
		commentForm.style.display = 'block';
		obj.appendChild(commentForm);
		
		var target_url = url('includes/content/blog2/blog_comment_form.php?post_id='+entry_id+'&comment_id='+comment_id);
		new Ajax.Updater(obj_id, target_url);
		
		var func = function(e) {
			// this prevents the onclick from passing to its parent element
			if (!e) var e = window.event;
			if (e) e.cancelBubble = true;
			if (e.stopPropagation) e.stopPropagation();
		};
		commentForm.onclick = func;
	}
	else
	{
		var commentObj = document.getElementById(obj_id);
		if (commentObj.style.display != 'block')
		{
			commentObj.style.display = 'block';
		}
		else
		{
			commentObj.style.display = 'none';
		}
	}
}

function Blog2_AddComment(form)
{
	var parent_id = form.elements.comment_id.value;
	var entry_id  = form.elements.post_id.value;
	form.elements.submitbtn.disabled = true;
	
	new Ajax.Form(form, { onComplete: function(t) {
		var response = t.responseText;
		var flag = response.substring(0, 1);
		
		if (flag == '2')
		{
			// reload comments, zoom to id of post
			var comment_id = response.substring(2);
			var obj_id = 'blog_comment_' + entry_id + '_' + parent_id;
			document.getElementById(obj_id).style.display = 'none'; // close comment window
			
			Blog2_ReloadComments(entry_id, comment_id);
		}
		else if (flag == '1')
		{
			var error = response.substring(2);
			alert(error);
			form.elements.submitbtn.disabled = false;
		}
		else
		{
			var obj_id = 'blog_comment_' + entry_id + '_' + parent_id;
			document.getElementById(obj_id).style.display = 'none'; // close comment window
			var message = response.substring(2);
			alert(message);
		}
	} } );
}

function Blog2_ReloadComments(post_id, comment_id)
{
	var comment_container = 'comment_container_' + post_id;
	var target_url = url('includes/content/blog2/comments.php?post_id='+post_id+'&page_action=reload_comments');
	new Ajax.Updater(comment_container, target_url, { onComplete: function() {
		if (comment_id != null)
		{
			var obj2 = document.getElementById('blogcomment_'+comment_id);
			obj2.scrollIntoView();
		}
	} });
}


/*
function AddComment(form)
{
	var post_id = form.elements.post_id.value;
	//form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function(t) {
		var response = t.responseText;
		var flag = response.substring(0, 1);
		if (flag == '0')
		{
			var inner = response.substring(2);
			form.parentNode.innerHTML = inner;
		}
		else if (flag == '1')
		{
			var error = response.substring(2);
			alert(error);
		}
		else
		{
			var comment_id = response.substring(2);
			Blog2_ReloadComments(post_id, comment_id);
			
			var comment_form = document.getElementById('comment_' + post_id);
			comment_form.innerHTML = 'Thank you!';
			
			//form.reset();
			//form.elements.parent.value = '0';
		}
		//form.elements.submitbtn.disabled = false;
	} } );
}


function ToggleComment(post_id)
{
	var obj = document.getElementById('comment_' + post_id);
	if (obj.style.display == 'none')
	{
		obj.style.display = 'block';
	}
	else
	{
		obj.style.display = 'none';
	}
}

function ShowComments(post_id)
{
	var obj = document.getElementById('comment_show_' + post_id);
	if (obj.style.display == 'block')
	{
		obj.style.display = 'none';
	}
	else
	{
		obj.style.display = 'block';
	}
}

function Blog2_Reply(post_id, comment_id)
{
	var obj = document.getElementById('comment_' + post_id);
	obj.style.display = 'block';
	
	var form = document.getElementById('comment_form_' + post_id);
	form.elements.parent.value = comment_id;
	form.elements.verify.focus();
	form.elements.name.focus();
}

*/