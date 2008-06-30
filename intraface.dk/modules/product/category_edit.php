<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */
require('../../include_first.php');

$module = $kernel->module('product');
$translation = $kernel->getTranslation('product');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    
}

$page = new Intraface_Page($kernel);
$page->start(t('Edit category'));
?>

<h1><?php e(t('Edit category')); ?></h1>

<?php echo $category->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset>
    <legend><?php e(t('Category information')); ?></legend>
        <input type="hidden" name="id" value="<?php if(isset($values['id'])) e($values['id']); ?>" />
        <div class="formrow">
            <label for="name"><?php e(t('Name')); ?></label>
            <input type="text" name="name" id="name" value="<?php if (isset($values['name'])) e($values['name']); ?>" />
        </div>
    </fieldset>
    
    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" class="save" /> <?php e(t('or', 'common')); ?>
        <a href="attribute_groups.php"><?php e(t('regret', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>
