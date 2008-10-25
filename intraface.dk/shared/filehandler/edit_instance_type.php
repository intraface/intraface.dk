<?php
require('../../include_first.php');
$shared_filehandler = $kernel->useShared('filehandler');
$translation = $kernel->getTranslation('filehandler');
$shared_filehandler->includeFile('InstanceManager.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['submit'])) {
        $instance_manager = new InstanceManager($kernel, (int)$_POST['type_key']);

        if ($instance_manager->save($_POST)) {
            header('location: settings.php');
            exit;
        }
        $value = $_POST;
    }
    else {
        trigger_error("submit was not set!", E_USER_ERROR);
        exit;
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['type_key'])) {
        $instance_manager = new InstanceManager($kernel, (int)$_GET['type_key']);
        $value = $instance_manager->get();
    }
    else {
        $instance_manager = new InstanceManager($kernel);
        $value = $instance_manager->get();
    }
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('edit instance type'));

?>

<h1><?php e($translation->get('edit instance type')); ?></h1>

<?php echo $instance_manager->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

    <input type="hidden" name="type_key" value="<?php e($instance_manager->get('type_key')); ?>" />

    <fieldset>
        <legend><?php e($translation->get('instance')); ?></legend>

        <div class="formrow">
            <label for="name"><?php e($translation->get('name')); ?>:</label>
            <?php if ($instance_manager->get('type_key') > 0 && $instance_manager->get('origin') != 'custom'): ?>
                <?php if (isset($value['name'])): ?>
                    <div><?php e($value['name']); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <input type="input" name="name" id="name" value="<?php if (isset($value['name'])) e($value['name']); ?>" /> <span><?php e($translation->get('allowed characters')); ?>: a-z 0-9 _ -</span>
            <?php endif; ?>
        </div>

        <div class="formrow">
            <label for="max_width"><?php e($translation->get('maximum width')); ?>:</label>
            <input type="input" name="max_width" id="max_width" value="<?php if (isset($value['max_width'])) e($value['max_width']); ?>" />
        </div>

        <div class="formrow">
            <label for="max_height"><?php e($translation->get('maximum height')); ?>:</label>
            <input type="input" name="max_height" id="max_height" value="<?php if (isset($value['max_height'])) e($value['max_height']); ?>" />
        </div>

        <div class="formrow">
            <label for="resize_type"><?php e($translation->get('resize type')); ?>:</label>
            <select name="resize_type" id="resize_type">
                <?php foreach ($instance_manager->getResizeTypes() AS $resize_type): ?>
                    <option value="<?php e($resize_type); ?>" <?php if (isset($value['resize_type']) && $value['resize_type'] == $resize_type) e('selected="selected"'); ?> ><?php e($translation->get($resize_type)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <p>
        <input type="submit" class="submit" name="submit" value="<?php e($translation->get('save', 'common')); ?>" />
        <?php e($translation->get('or', 'common')); ?>
        <a href="settings.php"><?php e($translation->get('cancel', 'common')); ?></a>
    </p>
</form>

<?php
$page->end();
?>
