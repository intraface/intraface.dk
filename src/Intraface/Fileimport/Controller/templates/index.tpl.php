<h1><?php e(t('import contacts')); ?></h1>

<?php echo $fileimport->error->view(); ?>

<form action="<?php e(url()); ?>" method="post" enctype="multipart/form-data">

<?php if (isset($context->mode) && $context->mode = 'select_fields') : ?>
    <fieldset>
        <legend><?php e(t('select the fields for import')); ?></legend>
        <?php foreach ($context->values[0] as $key => $value) : ?>
            <div class="formrow">
                <label for="fields_<?php e($key); ?>"><?php e($value); ?></label>
                <select name="fields[<?php e($key); ?>]" id="fields_<?php e($key); ?>">
                    <option value="">[<?php e(t('ignore')); ?>]</option>
                    <?php foreach ($context->fields as $field) : ?>
                        <option value="<?php e($field); ?>"><?php e(t($field)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend><?php e(t('column header')); ?></legend>
        <div class="formrow">
            <label for="header"><?php e(t('dataset has column header')); ?></label>
            <input type="checkbox" name="header" id="header" value="1" />
        </div>
        <div style="clear:both;"><?php e(t('tip: if the fieldnames you see in the left column above is the first data record you want to import, your dataset does not have a header')); ?>.</div>
    </fieldset>

    <input type="hidden" name="file_id" value="<?php e($context->filehandler->get('id')); ?>" />

    <input type="submit" class="save" name="save" value="<?php e(t('select').'...'); ?>" />
    <a href="<?php url(null); ?>"><?php e(t('Cancel')); ?></a>

<?php else : ?>
    <fieldset>
        <legend><?php e(t('file')); ?></legend>

        <div><?php e(t('currently files in the CSV format are supported')); ?></div>

        <div class="formrow">
            <label for="userfile"><?php e(t('choose your file')); ?></label>
            <input name="userfile" type="file" id="userfile" />
        </div>
    </fieldset>

    <input type="submit" class="save" name="upload_file" value="<?php e(t('analyze file').'...'); ?>" />
    <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
<?php endif; ?>
</form>