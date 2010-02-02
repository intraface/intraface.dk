<h1><?php e(t('Edit attribute in group').' '.$group->getName()); ?></h1>

<?php echo $context->getError()->view(); ?>

<form action="<?php e(url(NULL, array($context->subview()))); ?>" method="post">
<fieldset>
    <legend><?php e(t('Attribute information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($attribute)) e($attribute->getId()); ?>" />
        <input type="hidden" name="group_id" value="<?php if (isset($group)) e($group->getId()); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($attribute)) e($attribute->getName()); ?>" />
        </div>
    </fieldset>

    <div>
        <input type="submit" name="submit" value="<?php e(t('Save')); ?>" class="save" />
        <a href="<?php e(url()); ?>"><?php e(t('Cancel')); ?></a>
    </div>

</form>