<h1><?php e(t('Upload files')); ?></h1>

<?php if (!empty($msg)) : ?>
    <p class="message"><?php echo $msg; ?></p>
<?php endif; ?>

<fieldset>
    <legend><?php e(t('Select files to upload')); ?></legend>
    <div id="iframe">
        <iframe frameborder="0" src="<?php e(url('../uploadscript')); ?>"></iframe>
    </div>
</fieldset>

<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <legend><?php e(t('Uploaded files')); ?></legend>
        <div id="images"></div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Keywords and permissions')); ?></legend>
        <div class="formrow">
            <label for=""><?php e(t('Keywords', 'keyword')); ?></label>
            <input type="text" name="keywords" value="<?php if (isset($_POST['keywords'])) {
                e($_POST['keywords']);
} ?>" />
        </div>

        <div class="formrow">
            <label for="accessibility"><?php e(t('Accessibility')); ?></label>
            <select name="accessibility">
                <option value="public"><?php e(t('Public')); ?></option>
                <option value="intranet"><?php e(t('Intranet')); ?></option>
            </select>
        </div>
    </fieldset>

    <p class="alert"><?php e(t('Do not click save, before all the files have been uploaded')); ?>.</p>

    <p>
        <input type="submit" value="<?php e(t('Save')); ?>" />
        <a href="<?php e($redirect->getRedirect(url('../'))); ?>"><?php e(t('Cancel')); ?></a>
    </p>
</form>