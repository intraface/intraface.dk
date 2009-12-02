<h1><?php e(t('import contacts')); ?></h1>

<?php echo $fileimport->error->view(); ?>

<form action="<?php e(url()); ?>" method="post" enctype="multipart/form-data">

<?php if (isset($mode) && $mode = 'select_fields'): ?>
    <fieldset>
        <legend><?php e(t('select the fields for import')); ?></legend>
        <?php foreach ($values[0] AS $key => $value): ?>
            <div class="formrow">
                <label for="fields_<?php e($key); ?>"><?php e($value); ?></label>
                <select name="fields[<?php e($key); ?>]" id="fields_<?php e($key); ?>">
                    <option value="">[<?php e(__('ignore', 'common')); ?>]</option>
                    <?php foreach ($fields AS $field): ?>
                        <option value="<?php e($field); ?>"><?php e(__($field, $translation_page_id)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend><?php e(__('column header')); ?></legend>
        <div class="formrow">
            <label for="header"><?php e(__('dataset has column header')); ?></label>
            <input type="checkbox" name="header" id="header" value="1" />
        </div>
        <div style="clear:both;"><?php e(__('tip: if the fieldnames you see in the left column above is the first data record you want to import, your dataset does not have a header')); ?>.</div>
    </fieldset>

    <input type="hidden" name="file_id" value="<?php e($filehandler->get('id')); ?>" />

    <input type="submit" class="save" name="save" value="<?php e(__('select', 'common').'...'); ?>" />
    <?php e(__('or', 'common')); ?>
    <a href="<?php echo 'index.php'; ?>"><?php e(__('Cancel', 'common')); ?></a>

<?php else: ?>
    <fieldset>
        <legend><?php e(__('file')); ?></legend>

        <div><?php e(__('currently files in the CSV format are supported')); ?></div>

        <div class="formrow">
            <label for="userfile"><?php e(__('choose your file')); ?></label>
            <input name="userfile" type="file" id="userfile" />
        </div>
    </fieldset>

    <input type="submit" class="save" name="upload_file" value="<?php e(__('analyze file').'...'); ?>" />
    <a href="<?php e(url('../')); ?>"><?php e(__('Cancel', 'common')); ?></a>
<?php endif; ?>
</form>