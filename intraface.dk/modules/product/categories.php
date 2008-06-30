<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // POST actions  
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // GET actions
    
}

$page = new Intraface_Page($kernel);
$page->start(t('Add product to categories in shop'));
?>
<h1><?php e(t('Add product to categories in shop')); ?></h1>

<ul class="options">
    <li><a class="new" href="attribute_group_edit.php"><?php e(t('Create category')); ?></a></li>
    <li><a href="product.php?id="><?php e(t('Close', 'common')); ?></a></li>
</ul>

<?php if ($groups->count() == 0): ?>
    <p><?php e(t('No categories has been created.')); ?> <a href="category_edit.php"><?php e(t('Create category')); ?></a>.</p>
<?php else: ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<?php if(!empty($deleted)): ?>
        <p class="message"><?php e(t('An categories has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('regret', 'common')); ?>" /></p>
<?php endif; ?>

    <table summary="<?php e(t('Categories')); ?>" id="categories_table" class="stripe">
        <caption><?php e(t('Categories')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php e(t('..')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories AS $category): ?>
                <tr>
                    <td>
                        <input type="checkbox" value="<?php echo intval($group->getId()); ?>" name="selected[]" />
                    </td>
                    <td></td>
                    <td class="options"><a class="edit" href="category_edit.php?id=<?php echo intval($category['id']); ?>"><?php e(t('edit', 'common')); ?></a></td>
                </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" value="<?php e(t('Add product to selected categories')); ?>" />
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