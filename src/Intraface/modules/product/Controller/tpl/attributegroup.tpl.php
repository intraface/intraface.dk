<h1><?php e(t('Attribute in group').' '.$group->getName()); ?></h1>

<p><?php e($group->getDescription()); ?></p>

<ul class="options">
    <li><a class="new" href="<?php e(url('.', array('create'))); ?>"><?php e(t('Create attribute')); ?></a></li>
    <li><a class="edit" href="<?php e(url('.', array('edit'))); ?>"><?php e(t('Edit group')); ?></a></li>
    <li><a class="delete" href="<?php e(url('.', array('delete'))); ?>"><?php e(t('Delete group')); ?></a></li>
    <li><a class="close" href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (!empty($deleted)): ?>
    <form action="<?php e(url()); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($group->getId()); ?>" />
        <p class="message"><?php e(t('An attribute has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel')); ?>" /></p>
    </form>
<?php endif; ?>

<?php if (count($attributes) == 0): ?>
    <p><?php e(t('No attributes has been created.')); ?> <a href="attribute_edit.php?group_id=<?php e($group->getId()); ?>"><?php e(t('Create attribute')); ?></a>.</p>
<?php else: ?>

<form action="<?php e(url()); ?>" method="post">
<input type="hidden" name="id" value="<?php e($group->getId()); ?>" />
    <table summary="<?php e(t('Attributes')); ?>" id="attribute_table" class="stripe">
        <caption><?php e(t('Attributes')); ?></caption>
        <thead>
            <tr>
                <!-- <th></th>  -->
                <th><?php e(t('Name')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attributes as $attribute): ?>
                <tr>
                    <!-- <td><input type="checkbox" value="<?php e($attribute->get('id')); ?>" name="selected[]" /></td>  -->
                    <td><?php e($attribute->getName()); ?></td>
                    <td class="options">
                        <a class="edit" href="<?php e(url($attribute->getId(), array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                        <a class="delete" href="<?php e(url($attribute->getId(), array('delete'))); ?>"><?php e(t('Delete')); ?></a>
                    </td>
                </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    <!-- <select name="action">
        <option value=""><?php e(t('choose...')); ?></option>
        <option value="delete"><?php e(t('delete selected')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('go')); ?>" />  -->
<?php endif; ?>
</form>
