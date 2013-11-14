<?php
if (!isset($instance_id)) { return; }
?>
<div class="ap_overflow">
	<h3><?=$p_action?> topic</h3>
	<form id="faq_form" method="post" action="<?=$body->url('includes/content/faq/submit.php')?>" onsubmit="FAQ_Submit(this); return false">
	<input type="hidden" id="instance_id"  name="instance_id" value="<?=$instance_id?>" />
	<input type="hidden" name="page_action" value="<?=$action?>" />
	<input type="hidden" name="answer" value="" />


	<?=$extra?>
	<table border="0" cellpadding="2" cellspacing="1" class="admin_list">
	<tr class="a">
		<td class="bold">Question</td>
		<td><input type="text" name="question" value="<?=$info['question']?>" /></td>
	</tr>
	<tr class="b">
		<td colspan="2">
			<p class="bold">Answer</p>
			<textarea id="faq_answer"><?=$info['answer']?></textarea>
		</td>
	</tr>
	</table>
	<input type="submit" class="co_button co_button1" value="<?=$button?>" name="submitbtn" />
	</form>

	<button class="co_button co_button2" onclick="FAQ_Reload('<?=$instance_id?>')">Cancel</button>
</div>