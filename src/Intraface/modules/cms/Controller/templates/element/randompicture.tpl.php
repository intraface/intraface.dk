<fieldset>
	<legend><?php e(t('Choose keywords')); ?></legend>
    <?php
        $context->getKernel()->useModule('filemanager');
        $filemanager = new Intraface_modules_filemanager_Filemanager($context->getKernel());
        if (!empty($value['keywords'])) {
            $selected_keywords = $value['keywords'];
        } else {
            $selected_keywords = array();
        }

        $appender = $filemanager->getKeywordAppender();
        $keywords = $appender->getUsedKeywords();
        if (count($keywords) > 0) {
            echo '<div>'. e(t('keywords', 'keyword')) . ': <ul style="display: inline;">';
            foreach ($keywords as $keyword_value) {
                if (in_array($keyword_value['keyword'], $selected_keywords) === true) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = "";
                }
                echo '<li style="display: inline; margin-left: 20px;"><label for="keyword_'.$keyword_value['id'].'"><input type="checkbox" name="keywords[]" value="'.$keyword_value['keyword'].'" id="keyword_'.$keyword_value['id'].'" '.$checked.' />&nbsp;'.$keyword_value['keyword'].'</label></li>';
        }
        echo '</ul></div>';
    }
    ?>
</fieldset>
<fieldset>
	<div class="formrow">
    	<label for="pic_size"><?php e(t('size')); ?></label>
        <?php
            $filehandler = new Filehandler($kernel);
            $filehandler->createInstance();
            $instances = $filehandler->instance->getList();
        ?>

        <select name="size">
        	<option value="original"<?php if (!empty($value['size']) AND $value['size'] == 'original') echo ' selected="selected"'; ?>><?php e(t('original', 'filehandler')); ?></option>
            <?php foreach ($instances AS $instance): ?>
            <option value="<?php e($instance['name']); ?>"<?php if (!empty($value['size']) AND $value['size'] == $instance['name']) echo ' selected="selected"'; ?>><?php e(t($instance['name'], 'filehandler')); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

</fieldset>