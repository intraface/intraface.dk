<fieldset>
	<legend><?php e(t('Choose picture')); ?></legend>
            <?php
                /*
                if (empty($value['pic_id'])) $value['pic_id'] = 0;
                $filehandler = new FileHandler($kernel, $value['pic_id']);
                $filehandler_html = new FileHandlerHTML($filehandler);
                $filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));
                */
                if (!empty($value['pic_id'])) {
                    $filehandler = new Ilib_Filehandler($kernel, $value['pic_id']);
                    e('Filehandler id: ' . $filehandler->get('id'). ' chosen');
                }
            ?>
     <input type="submit" value="<?php e(t('Choose picture')); ?>" name="choose_file" />
</fieldset>
<fieldset>
	<div class="formrow">
    	<label for="pic_size"><?php e(t('size')); ?></label>
        <?php
            $filehandler = new Filehandler($kernel);
            $filehandler->createInstance();
            $instances = $filehandler->instance->getList();
        ?>

        <select name="pic_size">
        	<option value="original"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == 'original') echo ' selected="selected"'; ?>><?php e(t('original', 'filehandler')); ?></option>
            <?php foreach ($instances AS $instance): ?>
            <option value="<?php e($instance['name']); ?>"<?php if (!empty($value['pic_size']) AND $value['pic_size'] == $instance['name']) echo ' selected="selected"'; ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="formrow">
        <label for="pic_text"><?php e(t('picture text')); ?></label>
    	<input name="pic_text" value="<?php if (!empty($value['pic_text'])) e($value['pic_text']); ?>" />
    </div>
	<div class="formrow">
        <label for="pic_url"><?php e(t('picture url')); ?></label>
    	<input name="pic_url" value="<?php if (!empty($value['pic_url'])) e($value['pic_url']); ?>" />
	</div>

</fieldset>