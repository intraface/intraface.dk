<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */

require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

$gateway = new Intraface_modules_product_Attribute_Group_Gateway();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['id'])) {
        $group = $gateway->findById($_POST['id']);
    }
    else {
        $group = new Intraface_modules_product_Attribute_Group;
    }
    
    $group->name = $_POST['name'];
    $group->description = $_POST['description'];
    try {
        $group->save();
        $group->load();
        header('Location: attribute_group.php?id='.$group->getId());
    }
    catch(Doctrine_Validator_Exception $e) {
        $error = new Intraface_Doctrine_ErrorRender($translation);
        $error->attachErrorStack($group->getErrorStack());
    }  
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['id'])) {
        $group = $gateway->findById($_GET['id']);
    }
}

$page = new Intraface_Page($kernel);
$page->start(t('Edit attribute group'));
?>

<h1><?php e(t('Edit attribute group')); ?></h1>

<?php if (isset($error)) echo $error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
    <legend><?php e(t('Attribute group information')); ?></legend>
        <input type="hidden" name="id" value="<?php if (isset($group)) e($group->getId()); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($group)) e($group->getName()); ?>" />
        </div>
        <div class="formrow">
            <label for="description"><?php e(t('Shor description')); ?></label>
            <input type="text" name="description" id="description" value="<?php if (isset($group)) e($group->getDescription()); ?>" />
        </div>
    </fieldset>
    
    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <a href="attribute_groups.php"><?php e(t('Cancel', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>
