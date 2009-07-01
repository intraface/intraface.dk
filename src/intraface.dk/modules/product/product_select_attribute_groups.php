<?php
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product = new Product($kernel, $_POST['id']);
    
    if (!empty($_POST['select'])) {
        
        $existing_groups = array();
        $new_groups = array();
        foreach ($product->getAttributeGroups() AS $group) $existing_groups[] = $group['id'];
        
        if (count($existing_groups) > 0) {
            try {
                $variations = $product->getVariations();
                if ($variations->count() > 0) {
                    $error = new Intraface_Error;
                    $error->set('You cannot change the attached attribute groups when variations has been created');
                }
            } catch (Intraface_Gateway_Exception $e) {
                
            }
        }
        
        if (!isset($error) || $error->isError() == 0) {
            if (isset($_POST['selected']) && is_array($_POST['selected'])) {
                $new_groups = $_POST['selected'];    
            }
            
            foreach (array_diff($existing_groups, $new_groups) AS $id) {
                $product->removeAttributeGroup($id);
            }
            
            foreach ($new_groups AS $id) {
                $product->setAttributeGroup($id);
            }
            
            $existing_groups = $new_groups;
            
            header('location: product_variations_edit.php?id='.$product->getId());
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $product = new Product($kernel, $_GET['id']);
    
    $existing_groups = array();
    foreach ($product->getAttributeGroups() AS $group) $existing_groups[] = $group['id'];
    
    if (count($existing_groups) > 0) {
        try {
            $variations = $product->getVariations();
            if ($variations->count() > 0) {
                
                $error = new Intraface_Error;
                $error->set('You cannot change the attached attribute groups when variations has been created');
            }
        } catch (Intraface_Gateway_Exception $e) {
            
        }
    }
    
}

$groups = $group_gateway->findAll();

$page = new Intraface_Page($kernel);
$page->start(t('Select attributes for product').' '.$product->get('name'));
?>
<h1><?php e(t('Select attributes for product').' '.$product->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a></li>
    <li><a href="product.php?id=<?php e($product->get('id')); ?>"><?php e(t('Close', 'common')); ?></a></li>
</ul>

<?php if (isset($error)) echo $error->view('html'); ?>

<?php if ($groups->count() == 0): ?>
    <p><?php e(t('No attribute groups has been created.')); ?> <a href="attribute_group_edit.php"><?php e(t('Create attribute group')); ?></a>.</p>
<?php else: ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
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
            <?php foreach ($groups AS $group): ?>
                <tr>
                    <td>
                        <input type="checkbox" id="product-attribute-<?php e($group->getId()); ?>" value="<?php e($group->getId()); ?>" name="selected[]" <?php if (in_array($group->getId(), $existing_groups)) echo 'checked="checked"'; ?> />
                    </td>
                    <td><?php e($group->getName()); ?></td>
                </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" name="select" value="<?php e(t('Select', 'common')); ?>" />
<?php endif; ?>
</form>


<?php
$page->end();
?>