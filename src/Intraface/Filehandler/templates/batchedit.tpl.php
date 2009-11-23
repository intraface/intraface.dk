<h1><?php e(__('files')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(__('Cancel', 'common')); ?></a></li>
</ul>



<form action="<?php e(url('./')); ?>" method="post">
<?php

foreach ($files as $file) {
    $gateway = new Ilib_Filehandler_Gateway($kernel);
    $this_filemanager = $gateway->getFromId($file['id']);
    if ($this_filemanager->get('is_picture')) {

    }
    $keyword_object = $this_filemanager->getKeywordAppender();
    $file['keywords'] = $keyword_object->getConnectedKeywordsAsString();
    ?>
    <table class="stripe">
    <caption><?php e(__('File')); ?></caption>
        <tbody>
            <tr>
                <td rowspan="5" style="width: 280px;">
                    <?php if ($this_filemanager->get('is_picture')): ?>
                        <?php $this_filemanager->createInstance('small');?>
                        <img src="<?php e($this_filemanager->instance->get('file_uri')); ?>" alt="" />
                    <?php else: ?>
                        <img src="<?php e($this_filemanager->get('icon_uri')); ?>" alt="" />
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php e(__('file')); ?></th>
                <td><?php e($file['file_name']); ?></td>
            </tr>
            <tr>
                <th><?php e(__('file description')); ?></th>
                <td><textarea style="width: 400px; height; 100px;" name="description[<?php e($file['id']); ?>]"><?php e($file['description']); ?></textarea></td>
            </tr>
            <tr>
                <th><?php e(__('keywords', 'keyword')); ?></th>
                <td><input type="text" name="keywords[<?php e($file['id']); ?>]" value="<?php e($file['keywords']); ?>" /></td>
            </tr>
            <tr>
                <th><?php e(__('file accessibility')); ?></th>
                <td><input type="radio" id="accessibility[<?php e($file['id']); ?>]_public" name="accessibility[<?php e($file['id']); ?>]" value="public" <?php if(isset($file['accessibility']) && $file['accessibility'] == 'public') e('checked="checked"'); ?> /><label for="accessibility[<?php e($file['id']); ?>]_public"><?php e(__('public')); ?></label> &nbsp; &nbsp; <input type="radio" id="accessibility[<?php e($file['id']); ?>]_intranet" name="accessibility[<?php e($file['id']); ?>]" value="intranet" <?php if(isset($file['accessibility']) && $file['accessibility'] == 'intranet') e('checked="checked"'); ?> /><label for="accessibility[<?php e($file['id']); ?>]_intranet"><?php e(__('intranet')); ?></label></td>
            </tr>
        </tbody>
    </table>
    <?php
}
?>
<p>
<input type="submit" value="<?php e(__('save', 'common')); ?>" />
<a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(__('Cancel', 'common')); ?></a>
</p>
</form>
