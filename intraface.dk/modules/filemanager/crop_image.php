<?php
require('../../include_first.php');

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if(isset($_POST['id']) && isset($_POST['instance_type'])) {
    $filemanager = new FileManager($kernel, $_POST['id']);
    $instance_type = $_POST['instance_type'];
    
    $validator = new Ilib_Validator($filemanager->error);
    $validator->isNumeric($_POST['width'], 'invalid width', 'greater_than_zero,integer');
    $validator->isNumeric($_POST['height'], 'invalid width', 'greater_than_zero,integer');
    $validator->isNumeric($_POST['x'], 'invalid width', 'zero_or_greater,integer');
    $validator->isNumeric($_POST['y'], 'invalid width', 'zero_or_greater,integer');
    
    if(!$filemanager->error->isError()) {
        $filemanager->createInstance($instance_type);
        $filemanager->instance->delete();
    
        $param['crop_width'] = (int)$_POST['width'];
        $param['crop_height'] = (int)$_POST['height'];
        $param['crop_offset_x'] = (int)$_POST['x'];
        $param['crop_offset_y'] = (int)$_POST['y'];

        $filemanager->createInstance($instance_type, $param);
        if(!$filemanager->error->isError()) {
            header('location: file.php?id='.$filemanager->get('id'));
            exit;
        }
    }   
} elseif (isset($_GET['id']) && isset($_GET['instance_type'])) {
    $filemanager = new FileManager($kernel, $_GET['id']);
    $instance_type = $_GET['instance_type'];
} else {
    trigger_error("an id and instance type is needed", E_USER_ERROR);
    exit;
}



$page = new Intraface_Page($kernel);

$img_height = $filemanager->get('height');
$img_width = $filemanager->get('width');

$filemanager->createInstance('system-large');
$editor_img_uri = $filemanager->instance->get('file_uri');
$editor_img_height = $filemanager->instance->get('height');
$editor_img_width = $filemanager->instance->get('width');

$size_ratio = $editor_img_width/$img_width;

$filemanager->createInstance($instance_type);
$type = $filemanager->instance->get('instance_properties');

$editor_min_width = $type['max_width'] * $size_ratio;
$editor_min_height = $type['max_height'] * $size_ratio;

if($editor_min_width > $editor_img_width) {
    $editor_min_width = $editor_img_width;
    $editor_min_height = ($editor_img_width/$editor_min_width)*$editor_min_height;
}

if($editor_min_height > $editor_img_height) {
    $editor_min_height = $editor_img_height;
    $editor_min_width = ($editor_img_height/$editor_min_height)*$editor_min_width;
}

if($type['resize_type'] != 'strict' && !empty($_GET['unlock_ratio'])) {
    $unlock_ratio = 1;
}
else {
    $unlock_ratio = 0;
}

$page->includeJavascript('module', 'cropper/lib/prototype.js');
$page->includeJavascript('module', 'cropper/lib/scriptaculous.js?load=builder,dragdrop');
$page->includeJavascript('module', 'cropper/cropper.js');
$page->includeJavascript('module', 'crop_image.js.php?size_ratio='.doubleval(1/$size_ratio).'&max_width='.round($editor_min_width).'&max_height='.round($editor_min_height).'&unlock_ratio='.$unlock_ratio);

$page->start($translation->get('crop image').' '.$filemanager->get('file_name'));
?>
<h1><?php echo safeToHtml($translation->get('crop image').' '.$translation->get('file')); ?></h1>

<ul class="options" style="clear:both;">
    <?php if($type['resize_type'] != 'strict' && $unlock_ratio == 1): ?>
        <li><a href="crop_image.php?id=<?php echo intval($filemanager->get('id')); ?>&instance_type=<?php echo safeToHtml($filemanager->instance->get('type')); ?>&unlock_ratio=1"><?php echo safeToHtml($translation->get('unlock image ratio')); ?></a></li>
    <?php elseif($type['resize_type'] != 'strict'): ?>
        <li><a href="crop_image.php?id=<?php echo intval($filemanager->get('id')); ?>&instance_type=<?php echo safeToHtml($filemanager->instance->get('type')); ?>&unlock_ratio=0"><?php echo safeToHtml($translation->get('lock image ratio')); ?></a></li>
    <?php endif; ?>
      
</ul>

<?php echo $filemanager->error->view(); ?>

<fieldset>
    <legend><?php echo safeToHtml($translation->get('cropping')); ?></legend>
    <form method="POST" action="crop_image.php">
    <input type="hidden" name="id" value="<?php echo intval($filemanager->get('id')); ?>" />
    <input type="hidden" name="instance_type" value="<?php echo safeToHtml($filemanager->instance->get('type')); ?>" />
    
    
    <div><?php echo safeTohtml($translation->get('crop')); ?>:  
        <label for="width"><?php echo safeTohtml($translation->get('width')); ?></label>
        <input type="text" name="width" id="width" value="" size="4" />
    
        <label for="height"><?php echo safeTohtml($translation->get('height')); ?></label>
        <input type="text" name="height" id="height" value="" size="4" />
    
    
        <?php echo safeTohtml($translation->get('from top left corner')); ?>
       
        <label for="x"><?php echo safeTohtml($translation->get('x')); ?></label>
        <input type="text" name="x" id="x" value="" size="4" />
        
        <label for="y"><?php echo safeTohtml($translation->get('y')); ?></label>
        <input type="text" name="y" id="y" value="" size="4" />

        <input type="submit" name="crop" id="submit" value="<?php echo safeTohtml($translation->get('crop and resize image')); ?>" />
    </div>
    <div><?php echo safeTohtml($translation->get('your original image has the following dimensions (width x height)')); ?>: <?php echo intval($img_width); ?> x <?php echo intval($img_height); ?></div>
    </form>
</fieldset>


<img id="image" src="<?php echo $editor_img_uri; ?>" width="<?php echo intval($editor_img_width); ?>" height="<?php echo intval($editor_img_height); ?>" />

<?php
$page->end();
?>