<h1><?php e(t('Upload file')); ?></h1>

<?php $filemanager->error->view(); ?>

<form action="<?php e(url('./')); ?>" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e(t('file')); ?></legend>
    <!--
    Overvej at g�re det muligt at uploade flere filer p� det samme filboks
    http://the-stickman.com/web-development/javascript/upload-multiple-files-with-a-single-file-element/
    -->
    <div class="formrow">
        <label for="userfile"><?php e(t('file')); ?></label>
        <input name="userfile" type="file" id="userfile" />
    </div>
    <div class="formrow">
        <label for="accessibility"><?php e(t('accessibility')); ?></label>
        <select name="accessibility">
            <option value="intranet"><?php e(t('intranet')); ?></option>
            <option value="public"><?php e(t('public')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="keyword"><?php e(t('keyword', 'keyword')); ?>:</label>
        <input type="text" name="keyword" id="keyword" value="" />
    </div>
</fieldset>

<input type="submit" class="save" name="submit" value="<?php e(t('upload')); ?>" />
<a href="<?php e($redirect->getRedirect(url('../'))); ?>"><?php e(t('Cancel')); ?></a>

</form>