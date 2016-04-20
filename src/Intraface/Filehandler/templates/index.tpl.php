<h1><?php e(t('File manager')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('upload')); ?>" onclick="location.href='<?php e(url('uploadmultiple')); ?>'; return false;"><?php e(t('Upload file')); ?></a></li>
    <!-- <li><a href="upload_multiple.php">Upload billeder</a></li> -->
    <li><a href="<?php e(url('sizes')); ?>"><?php e(t('Edit image sizes')); ?></a></li>
    <?php if (count($files) > 0) : ?>
    <li><a href="<?php e(url('batchedit', array('use_stored' => 'true'))); ?>"><?php e(t('Batch edit files')); ?></a></li>
    <?php endif; ?>
    <!--<li><a href="import.php"><?php e(t('Import files')); ?></a></li>-->
</ul>


<?php if (is_numeric($context->query('delete'))) : ?>
    <p class="message"><?php e(t('File has been deleted')); ?>. <a href="<?php e(url('./', array('undelete' => (int)$context->query('delete')))); ?>"><?php e(t('Cancel')); ?></a></p>
<?php endif; ?>


<?php if (empty($files) and !$context->query('search')) : ?>
    <p><?php e(t('No files uploaded')); ?></p>
<?php else : ?>


<form method="get" action="<?php e(url('./')); ?>">
    <fieldset>
        <legend><?php e(t('Search')); ?></legend>
        <label><?php e(t('Search text')); ?>:
            <input type="text" name="text" value="<?php e($filemanager->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label><?php e(t('Search filter')); ?>:
        <select name="filtration">
            <option value="0"><?php e(t('all')); ?></option>
            <option value="1"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 1) {
                e(' selected="selected"');
}?>><?php e(t('uploaded today')); ?></option>
            <option value="2"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 2) {
                e(' selected="selected"');
}?>><?php e(t('uploaded yesterday')); ?></option>
            <option value="3"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 3) {
                e(' selected="selected"');
}?>><?php e(t('uploaded this week')); ?></option>
            <option value="4"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 4) {
                e(' selected="selected"');
}?>><?php e(t('edited today')); ?></option>
            <option value="5"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 5) {
                e(' selected="selected"');
}?>><?php e(t('edited yesterday')); ?></option>
            <option value="6"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 6) {
                e(' selected="selected"');
}?>><?php e(t('public accessible')); ?></option>
            <option value="7"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 7) {
                e(' selected="selected"');
}?>><?php e(t('only accessible from intranet')); ?></option>

        </select>
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(t('Search')); ?>" />
        </span>

        <?php
        if (count($keywords) > 0) {
            echo '<div>'. e(t('keywords', 'keyword')) . ': <ul style="display: inline;">';
            foreach ($keywords as $value) {
                if (in_array($value['id'], $selected_keywords) === true) {
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

<table class="stripe">
    <caption><?php e(t('files')); ?></caption>
    <thead>
        <tr>
            <th></th>
            <th><?php e(t('file name')); ?></th>
            <th><?php e(t('file type')); ?></th>
            <th><?php e(t('file accessibility')); ?></th>
            <th><?php e(t('file size')); ?></th>
            <th><?php e(t('file date')); ?></th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php
        foreach ($files as $file) {
            ?>
            <tr>
                <td style="height: 67px;"><a href="<?php e($file['file_uri']); ?>"><img src="<?php e($file["icon_uri"]); ?>" style="height: <?php e($file["icon_height"]); ?>px; width: <?php e($file["icon_width"]); ?>px;" /></a></td>
                <td><a href="<?php e(url($file["id"])); ?>"><?php e($file["file_name"]); ?></a>
                    <br /><i><?php e(substr(strip_tags($file["description"]), 0, 100));
                    if (strlen(strip_tags($file["description"])) > 100) {
                        print('...');
                    } ?></i>
                </td>
                <td style="white-space: nowrap;"><?php e($file["file_type"]['description']); ?></td>
                <td style="white-space: nowrap;"><?php e(t($file["accessibility"])); ?></td>
                <td style="white-space: nowrap;"><?php e($file["dk_file_size"]); ?></td>
                <td style="white-space: nowrap;"><?php e($file["dk_date_created"]); ?></td>
                <td style="width: 120px;" class="options">
                    <a class="edit" href="<?php e(url($file['id'], array('edit'))); ?>"><?php e(t('edit')); ?></a>
                    <a class="delete" href="<?php e(url($file['id'], array('delete'))); ?>"><?php e(t('delete')); ?></a></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php echo $filemanager->getDBQuery()->display('paging'); ?>

<?php endif; ?>