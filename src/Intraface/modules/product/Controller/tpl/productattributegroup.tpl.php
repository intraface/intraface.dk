<h1><?php e(t('Select attributes for product').' '.$product->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (isset($error)) {
    echo $error->view('html');
} ?>

<?php if ($groups->count() == 0) : ?>
    <p><?php e(t('No attribute groups has been created.')); ?> <a href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a>.</p>
<?php else : ?>

<form action="<?php e(url()); ?>" method="post">
<input type="hidden" name="id" value="<?php e($product->getId()); ?>" />
    <table summary="<?php e(t('Attribute groups')); ?>" id="attribute_group_table" class="stripe">
        <caption><?php e(t('Attribute groups')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('Attribute group')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $group) : ?>
                <tr>
                    <td>
                        <input type="checkbox" id="product-attribute-<?php e($group->getId()); ?>" value="<?php e($group->getId()); ?>" name="selected[]" <?php if (in_array($group->getId(), $existing_groups)) {
                            echo 'checked="checked"';
} ?> />
                    </td>
                    <td><?php e($group->getName());
                    if ($group->getDescription() != '') {
                        e(' ('.$group->getDescription().')');
                    } ?></td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" name="select" value="<?php e(t('Select')); ?>" />
<?php endif; ?>
</form>