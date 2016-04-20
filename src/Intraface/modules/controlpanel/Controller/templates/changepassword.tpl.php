<h1><?php e(t('change user password')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('close')); ?></a></li>
</ul>

<?php echo $context->getUser()->error->view(); ?>

<form action="<?php e(url(null)); ?>" method="post">
<input type="hidden" name="_method" value="put" />
<fieldset>
    <legend><?php e(t('change password')); ?></legend>
    <div class="formrow">
        <label for="old-password"><?php e(t('old password')); ?></label>
        <input type="password" name="old_password" id="old-password" value="" />
    </div>

    <div class="formrow">
        <label for="new-password"><?php e(t('new password')); ?></label>
        <input type="password" name="new_password" id="new-password" value="" />
    </div>
    <div class="formrow">
        <label for="repeat-password"><?php e(t('repeat new password')); ?></label>
        <input type="password" name="repeat_password" id="repeat-password" value="" />
    </div>
</fieldset>

<p><input type="submit" name="submit" value="<?php e(t('save')); ?>" />
<a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a></p>

</form>
