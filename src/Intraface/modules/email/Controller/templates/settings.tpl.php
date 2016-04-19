<h1><?php e(t('Settings')); ?></h1>

<?php echo $error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

    <fieldset>
        <legend><?php e(t('Signature on e-mail')); ?></legend>

        <div class="formrow">
            <label for="signature_type"><?php e(t('Signature')); ?></label>
            <select name="signature_type">
                <option value="0" <?php if (isset($values['signature_type']) && $values['signature_type'] == 0) {
                    echo 'selected="selected"';
} ?> ><?php e(__('None')); ?></option>
                <option value="1" <?php if (isset($values['signature_type']) && $values['signature_type'] == 1) {
                    echo 'selected="selected"';
} ?> ><?php e(__('Standard')); ?></option>
                <option value="2" <?php if (isset($values['signature_type']) && $values['signature_type'] == 2) {
                    echo 'selected="selected"';
} ?> ><?php e(__('Custom')); ?></option>
            </select>
        </div>

        <div class="formrow">
            <label for="custom_signature"><?php e(t('Custom signature')); ?></label>
            <textarea name="custom_signature" cols="60" rows="4"><?php e($values['custom_signature']); ?></textarea>
        </div>
    </fieldset>

    <div>
        <input type="submit" name="submit" value="<?php e(t('Save')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>
