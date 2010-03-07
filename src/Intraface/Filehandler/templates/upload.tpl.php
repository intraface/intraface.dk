<h1><?php e(t('Upload file')); ?></h1>

<?php $filemanager->error->view(); ?>

<form action="<?php e(url('./')); ?>" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e(t('File')); ?></legend>
    <div class="formrow">
        <label for="userfile"><?php e(t('File')); ?></label>
        <input name="userfile" type="file" id="userfile" />
    </div>
    <div class="formrow">
        <label for="accessibility"><?php e(t('Accessibility')); ?></label>
        <select name="accessibility">
            <option value="intranet"><?php e(t('intranet')); ?></option>
            <option value="public"><?php e(t('public')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="keyword"><?php e(t('Keyword')); ?>:</label>
        <input type="text" name="keyword" id="keyword" value="" />
    </div>
</fieldset>

<input type="submit" class="save" name="submit" value="<?php e(t('upload')); ?>" />
<a href="<?php e($redirect->getRedirect(url('../'))); ?>"><?php e(t('Cancel')); ?></a>

</form>