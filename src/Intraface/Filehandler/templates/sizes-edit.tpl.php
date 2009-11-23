<h1><?php e(__('Edit instance type')); ?></h1>

<?php $instance_manager->error->view(); ?>

<form action="<?php e(url('./')); ?>" method="post">

    <input type="hidden" name="type_key" value="<?php e($instance_manager->get('type_key')); ?>" />

    <fieldset>
        <legend><?php e(__('Instance')); ?></legend>

        <div class="formrow">
            <label for="name"><?php e(__('Name')); ?>:</label>
            <?php if($instance_manager->get('type_key') > 0 && $instance_manager->get('origin') != 'custom'): ?>
                <?php if(isset($value['name'])) echo '<div>'.htmlentities($value['name']).'</div>'; ?>
            <?php else: ?>
                <input type="input" name="name" id="name" value="<?php if(isset($value['name'])) e($value['name']); ?>" /> <span><?php e(__('allowed characters')); ?>: a-z 0-9 _ -</span>
            <?php endif; ?>
        </div>

        <div class="formrow">
            <label for="max_width"><?php e(__('Maximum width')); ?>:</label>
            <input type="input" name="max_width" id="max_width" value="<?php if(isset($value['max_width'])) echo e($value['max_width']); ?>" />
        </div>

        <div class="formrow">
            <label for="max_height"><?php e(__('Maximum height')); ?>:</label>
            <input type="input" name="max_height" id="max_height" value="<?php if(isset($value['max_height'])) echo e($value['max_height']); ?>" />
        </div>

        <div class="formrow">
            <label for="resize_type"><?php e(__('Resize type')); ?>:</label>
            <select name="resize_type" id="resize_type">
                <?php foreach($instance_manager->getResizeTypes() as $resize_type): ?>
                    <option value="<?php e($resize_type); ?>" <?php if(isset($value['resize_type']) && $value['resize_type'] == $resize_type) e('selected="selected"'); ?> ><?php e(__($resize_type)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <p>
        <input type="submit" class="submit" name="submit" value="<?php e(__('Save', 'common')); ?>" />
        <?php e(__('or', 'common')); ?>
        <a href="<?php e(url('../')); ?>"><?php e(__('Cancel', 'common')); ?></a>
    </p>
</form>
