<?php
chdir('../../../');
require_once('core.php');
require_once('includes/captcha.class.php');

$post_id = $_GET['post_id'];
$comment_id = $_GET['comment_id'];

if ( (!is_numeric($post_id)) or (!is_numeric($comment_id)) ) { exit(); }

$instance_id = md5('blog_' .  $post_id . '_' . $comment_id);
$captcha     = new Captcha($instance_id, $db, getenv('REMOTE_ADDR'));
$captcha_img = '<img src="'.$body->url($captcha->Image()).'" />';

$button_text = ($comment_id == 0) ? 'Add Comment' : 'Add Reply';
?>
<div class="comment_form">

<form method="post" action="<?=$body->url('includes/content/blog2/comments.php')?>" onsubmit="Blog2_AddComment(this); return false">
	<input type="hidden" name="post_id" value="<?=$post_id?>" />
	<input type="hidden" name="comment_id" value="<?=$comment_id?>" />
	<input type="hidden" name="page_action" value="post_new" />
	<table border="0" cellpadding="0" cellspacing="0" class="comment_form">
	<tr class="a">
		<td class="left">Your Name</td>
		<td class="right"><input type="text" class="text" name="name" value="" /></td>
	</tr>
	<tr class="b">
		<td class="left">Your Website</td>
		<td class="right"><input type="text" class="text"  name="url" value="" /></td>
	</tr>
	<tr class="a">
		<td class="left">Your E-mail<div class="small">(for admin's eyes only, not posted to site</div></td>
		<td class="right"><input type="text" class="text"  name="email" value="" /></td>
	</tr>
	<tr class="b">
		<td class="left">Message</td>
		<td class="right"><textarea name="message"></textarea></td>
	</tr>
	<tr class="a">
		<td class="left">Human Verification<div class="small">(Please type the above phrase into the box below)</small></td>
		<td class="right">
			<div class="captcha_img"><?=$captcha_img?></div>
			<input type="text" name="captcha" width="5" maxlength="5" value="" />
		</td>
	</tr>
	</table>
	<input type="submit" value="<?=$button_text?>" name="submitbtn" />
</form>

</div>