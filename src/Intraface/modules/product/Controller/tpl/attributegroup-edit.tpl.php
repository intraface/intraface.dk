<h1><?php e(t('Edit attribute group')); ?></h1>

<?php echo $context->getError()->view(); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="post">
<fieldset>
    <legend><?php e(t('Attribute group information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($group)) e($group->getId()); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($group)) e($group->getName()); ?>" />
        </div>
        <div class="formrow">
            <label for="description"><?php e(t('Short description')); ?></label>
            <input type="text" name="description" id="description" value="<?php if (isset($group)) e($group->getDescription()); ?>" />
        </div>
    </fieldset>

    <div>
        <input type="submit" name="save" value="<?php e(t('Save')); ?>" class="save" />
        <a href="<?php e(url('.')); ?>"><?php e(t('Cancel')); ?></a>
    </div>

</form>
