<h1><?php e(__('Crop image').': '.$filemanager->get('file_name')); ?></h1>

<ul class="options" style="clear:both;">
    <?php if($type['resize_type'] != 'strict' && $unlock_ratio == 1): ?>
        <li><a href="<?php e($this->url(null, array('instance_type' => $filemanager->instance->get('type'), 'unlock_ratio' => 0))); ?>"><?php e(__('Lock image ratio')); ?></a></li>
    <?php elseif($type['resize_type'] != 'strict'): ?>
        <li><a href="<?php e($this->url(null, array('instance_type' => $filemanager->instance->get('type'), 'unlock_ratio' => 1))); ?>"><?php e(__('Unlock image ratio')); ?></a></li>
    <?php endif; ?>

</ul>

<?php $filemanager->error->view(); ?>

<fieldset>
    <legend><?php e(__('Cropping')); ?></legend>
    <form method="POST" action="<?php e(url('./')); ?>">
    <input type="hidden" name="id" value="<?php e(intval($filemanager->get('id'))); ?>" />
    <input type="hidden" name="instance_type" value="<?php e($filemanager->instance->get('type')); ?>" />

    <div><?php e(__('Crop')); ?>:
        <label for="width"><?php e(__('Width')); ?></label>
        <input type="text" name="width" id="width" value="" size="4" />

        <label for="height"><?php e(__('Height')); ?></label>
        <input type="text" name="height" id="height" value="" size="4" />

        <?php e(__('From top left corner')); ?>

        <label for="x"><?php e(__('x')); ?></label>
        <input type="text" name="x" id="x" value="" size="4" />

        <label for="y"><?php e(__('y')); ?></label>
        <input type="text" name="y" id="y" value="" size="4" />

        <input type="submit" name="crop" id="submit" value="<?php e(__('Crop and resize image')); ?>" />
    </div>
    <div><?php e(__('Your original image has the following dimensions (width x height)')); ?>: <?php e($img_width); ?> x <?php e($img_height); ?></div>
    </form>
</fieldset>


<p><img id="image" src="<?php e($editor_img_uri); ?>" width="<?php e($editor_img_width); ?>" height="<?php e($editor_img_height); ?>" /></p>
