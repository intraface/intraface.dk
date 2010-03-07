<h1><?php e(t('Edit file')); ?></h1>

<?php echo $filemanager->error->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e(t('File information')); ?></legend>

    <div class="formrow">
        <label for="accessibility"><?php e(t('file accessibility')); ?></label>
        <select name="accessibility">
            <option value="public" <?php if($values['accessibility'] == 'public') e('selected="selected"'); ?> ><?php e(t('public', 'filehandler')); ?></option>
            <option value="intranet" <?php if($values['accessibility'] == 'intranet') e('selected="selected"'); ?> ><?php e(t('intranet', 'filehandler')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="description"><?php e(t('file description')); ?></label>
        <textarea name="description" id="description" style="width: 500px; height: 200px;"><?php e($values['description']); ?></textarea>
    </div>

</fieldset>

<fieldset>
    <legend><?php e(t('Replace file')); ?></legend>

    <div class="formrow">
        <label for="replace_file"><?php e(t('Choose file')); ?></label>
        <input name="replace_file" type="file" id="replace_file" />
    </div>

</fieldset>

<p><input type="submit" class="save" name="submit" value="<?php e(t('save')); ?>" />
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a></p>
<input type="hidden" name="id" value="<?php e($filemanager->get("id")); ?>" />

</form>
