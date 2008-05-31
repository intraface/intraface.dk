<?php
require('../../include_first.php');
$shared_filehandler = $kernel->useShared('filehandler');
$translation = $kernel->getTranslation('filehandler');
$shared_filehandler->includeFile('InstanceManager.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(!empty($_POST['submit'])) {
        $instance_manager = new InstanceManager($kernel, (int)$_POST['type_key']);
        
        if($instance_manager->save($_POST)) {
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
elseif($_SERVER['REQUEST_METHOD'] == 'GET') {
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

<h1><?php echo safeToHtml($translation->get('edit instance type')); ?></h1>

<?php echo $instance_manager->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

    <input type="hidden" name="type_key" value="<?php echo intval($instance_manager->get('type_key')); ?>" />
    
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('instance')); ?></legend>
        
        <div class="formrow">
            <label for="name"><?php echo safeToHtml($translation->get('name')); ?>:</label>
            <?php if($instance_manager->get('type_key') > 0 && $instance_manager->get('origin') != 'custom'): ?>
                <?php if(isset($value['name'])) echo '<div>'.safeToForm($value['name']).'</div>'; ?>
            <?php else: ?>
                <input type="input" name="name" id="name" value="<?php if(isset($value['name'])) echo safeToForm($value['name']); ?>" /> <span><?php echo safeToHtml($translation->get('allowed characters')); ?>: a-z 0-9 _ -</span>
            <?php endif; ?>
        </div>
        
        <div class="formrow">
            <label for="max_width"><?php echo safeToHtml($translation->get('maximum width')); ?>:</label>
            <input type="input" name="max_width" id="max_width" value="<?php if(isset($value['max_width'])) echo safeToForm($value['max_width']); ?>" />
        </div>
        
        <div class="formrow">
            <label for="max_height"><?php echo safeToHtml($translation->get('maximum height')); ?>:</label>
            <input type="input" name="max_height" id="max_height" value="<?php if(isset($value['max_height'])) echo safeToForm($value['max_height']); ?>" />
        </div>
        
        <div class="formrow">
            <label for="resize_type"><?php echo safeToHtml($translation->get('resize type')); ?>:</label>
            <select name="resize_type" id="resize_type">
                <?php foreach($instance_manager->getResizeTypes() AS $resize_type): ?>
                    <option value="<?php echo safeToHtml($resize_type); ?>" <?php if(isset($value['resize_type']) && $value['resize_type'] == $resize_type) echo safeToHtml('selected="selected"'); ?> ><?php echo safeToHtml($translation->get($resize_type)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
    </fieldset>
    
    <p>
        <input type="submit" class="submit" name="submit" value="<?php echo safeToForm($translation->get('save', 'common')); ?>" /> 
        <?php echo safeToHtml($translation->get('or', 'common')); ?> 
        <a href="settings.php"><?php echo safeToHtml($translation->get('cancel', 'common')); ?></a>
    </p>
</form>

<?php
$page->end();
?>
