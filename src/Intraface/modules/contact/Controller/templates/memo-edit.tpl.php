<?php
$value = $reminder->get();
?>
<h1><?php e(t('Edit reminder')); ?></h1>

<?php echo $reminder->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

<fieldset>
    <legend><?php e(t('Reminder date')); ?></legend>
    <div class="formrow">
        <label for="reminder_date"><?php e(t('Reminder date')); ?></label>
        <input type="text" name="reminder_date" id="reminder_date" value="<?php if (!empty($value['dk_reminder_date'])) {
            e($value['dk_reminder_date']);
} ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('Reminder information')); ?></legend>

    <div class="formrow">
        <label for="subject"><?php e(t('Subject')); ?></label>
        <input type="text" name="subject" id="subject" value="<?php if (!empty($value['subject'])) {
            e($value['subject']);
} ?>" />
    </div>

    <div class="formrow">
        <label for="description"><?php e(t('Description')); ?></label>
        <textarea name="description" id="description" style="width: 400px; height: 100px;"><?php if (!empty($value['description'])) {
            e($value['description']);
} ?></textarea>
    </div>
</fieldset>

<div>

    <input type="submit" name="submit" value="<?php e(t('Save')); ?>" id="save" class="save" />
    <a href="<?php e(url('../')); ?>" title="<?php e(t('Cancel')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>