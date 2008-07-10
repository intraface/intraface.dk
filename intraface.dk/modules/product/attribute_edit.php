<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$group_gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group = $group_gateway->findById($_POST['group_id']);
    
    if(!empty($_POST['id'])) {
        $attribute = $group->getAttribute($_POST['id']);
    }
    else {
        $attribute = $group->attribute[0];
    }
    $attribute->name = $_POST['name'];
        
    try {
        $attribute->save();
        $attribute->load();
        header('location: attribute_group.php?id='.intval($group->getId()));
        exit;
    }
    catch (Doctrine_Validator_Exception $e) {
        $error = new Intraface_Doctrine_ErrorRender($translation);
        $error->attachErrorStack($attribute->getErrorStack());
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $group = $group_gateway->findById($_GET['group_id']);
    if(!empty($_GET['id'])) {
        $attribute = $group->getAttribute($_GET['id']);
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('Edit attribute in group').' '.$group->getName());
?>

<h1><?php e(t('Edit attribute in group').' '.$group->getName()); ?></h1>

<?php if(isset($error)) echo $error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
    <legend><?php e(t('Attribute information')); ?></legend>
        <input type="hidden" name="id" value="<?php if(isset($attribute)) e($attribute->getId()); ?>" />
        <input type="hidden" name="group_id" value="<?php if(isset($group)) e($group->getId()); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($attribute)) e($attribute->getName()); ?>" />
        </div>
    </fieldset>
    
    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <a href="attribute_group.php?id=<?php e($group->getId()); ?>"><?php e(t('regret', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>
