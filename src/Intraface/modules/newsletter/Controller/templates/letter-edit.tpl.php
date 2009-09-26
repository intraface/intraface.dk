<?php
$value = $context->getValues();
?>

<h1>Rediger nyhedsbrev</h1>

<div class="message">
	<p>Send dine nyhedsbreve:</p>
	<ul>
		<li>Mellem kl. 8.00 og 15.00</li>
		<li>Når folk har fået løn</li>
		<li><strong>aldrig</strong> i ferier eller weekender</li>
	</ul>
</div>

<?php echo $context->getLetter()->error->view(); ?>

<form action="<?php e(url(null)); ?>" method="post">
	<fieldset>
	<input type="hidden" name="id" value="<?php e($context->getLetter()->get('id')); ?>" />
	<input type="hidden" name="list_id" value="<?php e($context->getLetter()->list->get('id')); ?>" />

	<div class="formrow">
		<label for="title">Titel</label>
		<input type="text" name="subject" size="60" value="<?php if (!empty($value['subject'])) e($value['subject']); ?>" />
	</div>
	<div class="formrow">
		<label for="">Tekst</label>
		<textarea name="text" cols="90" rows="20"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
	</div>
	<div class="formrow">
		<label for="title">Deadline</label>
		<input type="text" name="deadline" size="60" value="<?php if (!empty($value['deadline'])) e($value['deadline']); else e(date('Y-m-d H:i:s')); ?>" />
	</div>


	<div>
		<input type="submit" name="submit" value="Gem" class="save" />
		eller
		<a href="<?php e(url(null)); ?>">Fortryd</a>
	</div>
	</fieldset>
</form>
