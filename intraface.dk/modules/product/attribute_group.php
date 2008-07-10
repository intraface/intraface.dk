<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group = $gateway->findById($_POST['id']);
    
    if(!empty($_POST['action']) && $_POST['action'] == 'delete') {
        /**
         * @todo: undelete needs to be implemented
         */
        // $deleted = array();
        if(is_array($_POST['selected'])) {
            foreach($_POST['selected'] AS $id) {
                try {
                    $attribute = $group->getAttribute($id);
                    $attribute->delete();
                    // $deleted[] = $id;
                } catch (Intraface_Gateway_Exception $e) {/* we do nothing */ }
            }
        }
    }
    
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $group = $gateway->findById($_GET['id']);
}

$attributes = $group->getAttributes();

$page = new Intraface_Page($kernel);
$page->start(t('Attributes in group').' '.$group->getName());
?>
<h1><?php e(t('Attribute in group').' '.$group->getName()); ?></h1>

<ul class="options">
    <li><a class="new" href="attribute_edit.php?group_id=<?php e($group->getId()); ?>"><?php e(t('Create attribute')); ?></a></li>
    <li><a href="attribute_groups.php?id=<?php e($group->getId()); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<?php if(!empty($deleted)): ?>
    <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($group->getId()); ?>" />
        <p class="message"><?php e(t('An attribute has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('regret', 'common')); ?>" /></p>
    </form>
<?php endif; ?>

<?php if (count($attributes) == 0): ?>
    <p><?php e(t('No attributes has been created.')); ?> <a href="attribute_edit.php?group_id=<?php e($group->getId()); ?>"><?php e(t('Create attribute')); ?></a>.</p>
<?php else: ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<input type="hidden" name="id" value="<?php e($group->getId()); ?>" />
    <table summary="<?php e(t('Attributes')); ?>" id="attribute_table" class="stripe">
        <caption><?php e(t('Attributes')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('Name')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($attributes AS $attribute): ?>
                <tr>
                    <td>
                        <input type="checkbox" value="<?php echo intval($attribute->get('id')); ?>" name="selected[]" />
                    </td>
                    <td><?php echo safeToHtml($attribute->getName()); ?></td>
                    <td class="options"><a class="edit" href="attribute_edit.php?group_id=<?php echo intval($group->getId()); ?>&amp;id=<?php echo intval($attribute->getId()); ?>"><?php e(t('edit', 'common')); ?></a></td>
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