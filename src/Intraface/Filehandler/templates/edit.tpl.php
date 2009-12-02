<h1><?php e(__('edit file')); ?></h1>

<?php echo $filemanager->error->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e(__('file information')); ?></legend>

    <div class="formrow">
        <label for="accessibility"><?php e(__('file accessibility')); ?></label>
        <select name="accessibility">
            <option value="public" <?php if($values['accessibility'] == 'public') e('selected="selected"'); ?> ><?php e(__('public', 'filehandler')); ?></option>
            <option value="intranet" <?php if($values['accessibility'] == 'intranet') e('selected="selected"'); ?> ><?php e(__('intranet', 'filehandler')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="description"><?php e(__('file description')); ?></label>
        <textarea name="description" id="description" style="width: 500px; height: 200px;"><?php e($values['description']); ?></textarea>
    </div>

</fieldset>

<fieldset>
    <legend><?php e(__('Replace file')); ?></legend>

    <div class="formrow">
        <label for="replace_file"><?php e(__('Choose file')); ?></label>
        <input name="replace_file" type="file" id="replace_file" />
    </div>

</fieldset>

<p><input type="submit" class="save" name="submit" value="<?php e(__('save', 'common')); ?>" />
<a href="<?php e(url('../')); ?>"><?php e(__('Cancel', 'common')); ?></a></p>
<input type="hidden" name="id" value="<?php e($filemanager->get("id")); ?>" />

</form>
