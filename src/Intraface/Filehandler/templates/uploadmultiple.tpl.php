<h1><?php e(__('Upload files')); ?></h1>

<?php if (!empty($msg)): ?>
<p class="message"><?php echo $msg; ?></p>
<?php endif; ?>

<fieldset>
    <legend><?php e(__('select files to upload')); ?></legend>
    <div id="iframe">
        <iframe frameborder="0" src="<?php e(url('../uploadscript')); ?>"></iframe>
    </div>
</fieldset>

<form action="<?php e(url(null)); ?>" method="post">
    <fieldset>
        <legend><?php e(__('Uploaded files')); ?></legend>
        <div id="images"></div>
    </fieldset>

    <fieldset>
        <legend><?php e(__('Keywords and permissions')); ?></legend>
        <div class="formrow">
            <label for=""><?php e(__('Keywords', 'keyword')); ?></label>
            <input type="text" name="keywords" value="<?php if (isset($_POST['keywords'])) e($_POST['keywords']); ?>" />
        </div>

        <div class="formrow">
            <label for="accessibility"><?php e(__('Accessibility')); ?></label>
            <select name="accessibility">
                <option value="public"><?php e(__('Public')); ?></option>
                <option value="intranet"><?php e(__('Intranet')); ?></option>
            </select>
        </div>
    </fieldset>

    <p class="alert"><?php e(__('Only click save when all files are uploaded')); ?>.</p>


    <p>
        <input type="submit" value="<?php e(__('Save', 'common')); ?>" />
         <a href="<?php e($redirect->getRedirect(url('../'))); ?>"><?php e(__('Cancel', 'common')); ?></a>
    </p>

</form>