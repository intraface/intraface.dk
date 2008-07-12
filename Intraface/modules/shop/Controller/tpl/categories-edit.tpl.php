<form action="<?php e(url(null)); ?>" method="post">
<fieldset>
    <legend><?php e(t('Category information')); ?></legend>
        <input type="hidden" name="id" value="<?php if(isset($values['id'])) e($values['id']); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($values['name'])) e($values['name']); ?>" />
        </div>
        <div class="formrow">
            <label for="identifier"><?php e(t('Identifier')); ?></label>
            <input type="text" name="identifier" id="identifier" value="<?php if (isset($values['identifier'])) e($values['identifier']); ?>" />
        </div>
    </fieldset>
    
    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <a href="<?php e(url('../')); ?>"><?php e(t('regret', 'common')); ?></a>
    </div>

</form>