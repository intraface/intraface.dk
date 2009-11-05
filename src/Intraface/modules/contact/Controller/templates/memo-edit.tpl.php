<?php
$value = $reminder->get();
?>
<h1><?php e(__('Edit reminder')); ?></h1>

<?php echo $reminder->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

<fieldset>
	<legend><?php e(__('Reminder date')); ?></legend>
	<div class="formrow">
		<label for="reminder_date"><?php e(__('Reminder date')); ?></label>
		<input type="text" name="reminder_date" id="reminder_date" value="<?php if (!empty($value['reminder_date'])) e($value['reminder_date']); ?>" />
	</div>
</fieldset>

<fieldset>
	<legend><?php e(__('Reminder information')); ?></legend>

	<div class="formrow">
		<label for="subject"><?php e(__('Subject')); ?></label>
		<input type="text" name="subject" id="subject" value="<?php if (!empty($value['subject'])) e($value['subject']); ?>" />
	</div>

	<div class="formrow">
		<label for="description"><?php e(__('Description')); ?></label>
		<textarea name="description" id="description" style="width: 400px; height: 100px;"><?php if (!empty($value['description'])) e($value['description']); ?></textarea>
	</div>
</fieldset>

<div>

	<input type="submit" name="submit" value="<?php e(__('Save', 'common')); ?>" id="save" class="save" />
	<a href="<?php e(url('../')); ?>" title="<?php e(__('Cancel', 'common')); ?>"><?php e(__('Cancel', 'common')); ?></a>
	</div>
</form>