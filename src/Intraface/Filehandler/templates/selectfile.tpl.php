<h1><?php e(t('Choose file')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a href="<?php e(url('Upload')); ?>" onclick="location.href='<?php e(url('uploadmultiple')); ?>'; return false;"><?php e(t('upload file')); ?></a></li>
</ul>

<?php // echo $filemanager->error->view('html'); ?>

<form method="get" action="<?php e(url(null, array('use_stored' => true))); ?>">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>
        <label><?php e(t('text')); ?>:
            <input type="text" name="text" value="<?php echo $filemanager->getDBQuery()->getFilter("text"); ?>" />
        </label>
        <label><?php e(t('Filter')); ?>
        <select name="filtration">
            <option value="0"><?php e(t('All')); ?></option>
            <option value="1"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 1) echo ' selected="selected"';?>><?php e(t('uploaded today')); ?></option>
            <option value="2"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 2) echo ' selected="selected"';?>><?php e(t('uploaded yesterday')); ?></option>
            <option value="3"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 3) echo ' selected="selected"';?>><?php e(t('uploaded this week')); ?></option>
            <option value="4"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 4) echo ' selected="selected"';?>><?php e(t('edited today')); ?></option>
            <option value="5"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 5) echo ' selected="selected"';?>><?php e(t('edited yesterday')); ?></option>
        </select>
        </label>
        <label><?php e(t('Only pictures')); ?>:
            <input type="checkbox" name="images" value="1" <?php if($filemanager->getDBQuery()->getFilter("images") == 1) echo 'checked="checked"'; ?> />
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(t('find')); ?>" />
        </span>

        <?php
        $selected_keywords = $filemanager->getDBQuery()->getKeyword();
        $keyword = $filemanager->getKeywordAppender();
        $keywords = $keyword->getUsedKeywords();

        if(count($keywords) > 0) {
            echo '<div>Nøgleord: <ul style="display: inline;">';
            foreach ($keywords AS $value) {
                if(in_array($value['id'], $selected_keywords) === true) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = "";
                }
                echo '<li style="display: inline; margin-left: 20px;"><label for="keyword_'.$value['id'].'"><input type="checkbox" name="keyword[]" value="'.$value['id'].'" id="keyword_'.$value['id'].'" '.$checked.' />&nbsp;'.$value['keyword'].'</label></li>';
            }
        echo '</ul></div>';
        }
        ?>
    </fieldset>
</form>

<?php echo $filemanager->getDBQuery()->display('character'); ?>
<form method="POST" action="<?php e(url(null)); ?>">
<table class="stripe">
    <caption><?php e(t('Files')); ?></caption>
    <thead>
        <tr>
            <th></th>
            <th></th>
            <th><?php e(t('File name')); ?></th>
            <th><?php e(t('File type')); ?></th>
            <th><?php e(t('Accessibility')); ?></th>
            <th><?php e(t('File size')); ?></th>
            <th><?php e(t('File date')); ?></th>
            <!--<th></th>-->
        </tr>
    </thead>

    <tbody>
        <?php foreach ($files as $file) { ?>
            <tr>
                <td>
                    <input type="<?php if($context->multiple_choice): e('checkbox'); else: print('radio'); endif; ?>" value="<?php echo $file["id"]; ?>" id="<?php echo $file["id"]; ?>" class="input-select_file" name="selected[]" <?php if(in_array($file['id'], $selected_files)) print("checked=\"checked\""); ?> />
                </td>
                <td style="height: 67px;"><img src="<?php e($file["icon_uri"]); ?>" style="height: <?php e($file["icon_height"]); ?>px; width: <?php e($file["icon_width"]); ?>px;" /></td>

                <td><a href="<?php e(url($file["id"])); ?>"><?php e($file["file_name"]); ?></a></td>
                <td><?php e($file["file_type"]['description']); ?></td>
                <td><?php e($file["accessibility"]); ?></td>
                <td><?php e($file["dk_file_size"]); ?></td>
                <td><?php e($file["date_created"]); ?></td>
                <!--<td class="buttons"><a href="<?php e($file['file_uri']); ?>" target="_blank">Hent fil</a></td>-->
            </tr>
        <?php } ?>
    </tbody>
</table>

<div>

    <?php if($context->multiple_choice): ?>
        <input type="submit" name="submit" id="submit-select_file" value="<?php e(t('Transfer selection')); ?>" />
    <?php endif; ?>

    <input type="submit" name="submit_close" id="submit_close-select_file" value="<?php e(t('Transfer selection and close')); ?>" />
    <a href="<?php e(url('../../')); ?>"><?php e(t('Cancel')); ?></a>
</div>

</form>

<?php echo $filemanager->getDBQuery()->display('paging'); ?>