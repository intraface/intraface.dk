<h1><?php e(__('Upload file')); ?></h1>

<?php $filemanager->error->view(); ?>

<form action="<?php e(url('./')); ?>" method="POST" enctype="multipart/form-data">
<fieldset>
    <legend><?php e(__('file')); ?></legend>
    <!--
    Overvej at gøre det muligt at uploade flere filer på det samme filboks
    http://the-stickman.com/web-development/javascript/upload-multiple-files-with-a-single-file-element/
    -->
    <div class="formrow">
        <label for="userfile"><?php e(__('file')); ?></label>
        <input name="userfile" type="file" id="userfile" />
    </div>
    <div class="formrow">
        <label for="accessibility"><?php e(__('accessibility')); ?></label>
        <select name="accessibility">
            <option value="intranet"><?php e(__('intranet')); ?></option>
            <option value="public"><?php e(__('public')); ?></option>
        </select>
    </div>

    <div class="formrow">
        <label for="keyword"><?php e(__('keyword', 'keyword')); ?>:</label>
        <input type="text" name="keyword" id="keyword" value="" />
    </div>
</fieldset>

<input type="submit" class="save" name="submit" value="<?php e(__('upload')); ?>" />
<a href="<?php e($redirect->getRedirect(url('../'))); ?>"><?php e(__('Cancel', 'common')); ?></a>

</form>