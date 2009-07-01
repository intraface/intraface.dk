<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] == 'delete') {
        $deleted = array();
        if (is_array($_POST['selected'])) {
            foreach ($_POST['selected'] AS $id) {
                try {
                    $group = $gateway->findById($id);
                    $group->delete();
                    $deleted[] = $id;
                } catch (Intraface_Gateway_Exception $e) {/* we do nothing */ }
            }
        }
    }
    
    if (!empty($_POST['undelete'])) {
        $undelete = (array)unserialize(base64_decode($_POST['deleted']));
        foreach ($undelete AS $id) {
            try {
                $group = $gateway->findDeletedById($id);
                $group->undelete();
            }
            catch (Intraface_Gateway_Exception $e) { }
        }
    }
    
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
}

$groups = $gateway->findAll();

$page = new Intraface_Page($kernel);
$page->start(t('Product attribute groups'));
?>
<h1><?php e(t('Product attribute groups')); ?></h1>

<ul class="options">
    <li><a class="new" href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a></li>
    <li><a href="index.php"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<?php if (!empty($deleted)): ?>
        <p class="message"><?php e(t('An attribute group has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel', 'common')); ?>" /></p>
<?php endif; ?>
</form>

<?php if ($groups->count() == 0): ?>
    <p><?php e(t('No attribute groups has been created.')); ?> <a href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a>.</p>
<?php else: ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
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
                        <input type="checkbox" value="<?php e($group->getId()); ?>" name="selected[]" />
                    </td>
                    <td><a href="attribute_group.php?id=<?php e($group->getId()); ?>"><?php e($group->getName()); ?></a></td>
                    <td class="options"><a class="edit" href="attribute_group_edit.php?id=<?php e($group->getId()); ?>"><?php e(t('edit', 'common')); ?></a></td>
                </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    <select name="action">
        <option value=""><?php e(t('choose...', 'common')); ?></option>
        <option value="delete"><?php e(t('delete selected', 'common')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('go', 'common')); ?>" />
<?php endif; ?>
</form>


<?php
$page->end();
?>