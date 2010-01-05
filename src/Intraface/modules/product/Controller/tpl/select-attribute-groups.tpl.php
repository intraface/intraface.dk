<h1><?php e(t('Product attribute groups')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create attribute group')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="post">
<?php if (!empty($deleted)): ?>
        <p class="message"><?php e(t('An attribute group has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel', 'common')); ?>" /></p>
<?php endif; ?>
</form>

<?php echo $context->getError()->view(); ?>

<?php if ($groups->count() == 0): ?>
    <p><?php e(t('No attribute groups has been created.')); ?> <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create attribute group')); ?></a>.</p>
<?php else: ?>

<form action="<?php e(url()); ?>" method="post">
   	<input type="hidden" name="_method" value="put" />
    <table summary="<?php e(t('Attribute groups')); ?>" id="attribute_group_table" class="stripe">
        <caption><?php e(t('Attribute groups')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('Group')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group): ?>
                <tr>
                    <td>
                        <input type="checkbox" value="<?php e($group->getId()); ?>" id="product-attribute-<?php e($group->getId()); ?>" <?php if(in_array($group->getId(), $context->existing_groups)) echo 'checked="checked"'; ?> name="selected[]" />
                    </td>
                    <td><a href="<?php e(url($group->getId())); ?>"><?php e($group->getName()); if($group->getDescription() != '') e(' ('.$group->getDescription().')'); ?></a></td>
                    <td class="options"><a class="edit" href="<?php e(url($group->getId(), array('edit'))); ?>"><?php e(t('Edit', 'common')); ?></a></td>
                </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" name="select" value="<?php e(t('Select', 'common')); ?>" />
<?php endif; ?>
</form>
