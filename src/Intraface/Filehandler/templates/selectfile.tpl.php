<h1><?php e(__('files')); ?></h1>

<ul class="options">
    <li><a href="select_file.php?upload=single" onclick="location.href='select_file.php?upload=multiple'; return false;"><?php e(__('upload file')); ?></a></li>
</ul>

<?php echo $filemanager->error->view('html'); ?>

<form method="get" action="<?php e($this->url(null, array('use_stored' => true))); ?>">
    <fieldset>
        <legend><?php e(__('search')); ?></legend>
        <label><?php e(__('text')); ?>:
            <input type="text" name="text" value="<?php echo $filemanager->getDBQuery()->getFilter("text"); ?>" />
        </label>
        <label>Filtrering:
        <select name="filtration">
            <option value="0">Alle</option>
            <option value="1"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 1) echo ' selected="selected"';?>><?php e(__('uploaded today')); ?></option>
            <option value="2"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 2) echo ' selected="selected"';?>><?php e(__('uploaded yesterday')); ?></option>
            <option value="3"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 3) echo ' selected="selected"';?>><?php e(__('uploaded this week')); ?></option>
            <option value="4"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 4) echo ' selected="selected"';?>><?php e(__('edited today')); ?></option>
            <option value="5"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 5) echo ' selected="selected"';?>><?php e(__('edited yesterday')); ?></option>
        </select>
        </label>
        <label><?php e(__('only pictures')); ?>:
            <input type="checkbox" name="images" value="1" <?php if($filemanager->getDBQuery()->getFilter("images") == 1) echo 'checked="checked"'; ?> />
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(__('find')); ?>" />
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
                }
                else {
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
<form method="POST" action="<?php e($this->url(null)); ?>">
<table class="stripe">
    <caption><?php e(__('files')); ?></caption>
    <thead>
        <tr>
            <th></th>
            <th></th>
            <th><?php e(__('file name')); ?></th>
            <th><?php e(__('file type')); ?></th>
            <th><?php e(__('accessibility')); ?></th>
            <th><?php e(__('file size')); ?></th>
            <th><?php e(__('file date')); ?></th>
            <!--<th></th>-->
        </tr>
    </thead>

    <tbody>
        <?php

        for($i = 0, $max = count($files); $i < $max; $i++) {
            ?>
            <tr>
                <td>
                    <input type="<?php if($multiple_choice): e('checkbox'); else: print('radio'); endif; ?>" value="<?php echo $files[$i]["id"]; ?>" id="<?php echo $files[$i]["id"]; ?>" class="input-select_file" name="selected[]" <?php if(in_array($files[$i]['id'], $selected_files)) print("checked=\"checked\""); ?> />
                </td>
                <td style="height: 67px;"><img src="<?php e($files[$i]["icon_uri"]); ?>" style="height: <?php e($files[$i]["icon_height"]); ?>px; width: <?php e($files[$i]["icon_width"]); ?>px;" /></td>

                <td><a href="file.php?id=<?php e($files[$i]["id"]); ?>"><?php e($files[$i]["file_name"]); ?></a></td>
                <td><?php e($files[$i]["file_type"]['description']); ?></td>
                <td><?php e($files[$i]["accessibility"]); ?></td>
                <td><?php e($files[$i]["dk_file_size"]); ?></td>
                <td><?php e($files[$i]["date_created"]); ?></td>
                <!--<td class="buttons"><a href="<?php e($files[$i]['file_uri']); ?>" target="_blank">Hent fil</a></td>-->
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<input type="hidden" name="redirect_id" id="redirect_id" value="<?php e($receive_redirect->get('id')); ?>" />

<div>

    <?php if($multiple_choice): ?>
        <input type="submit" name="submit" id="submit-select_file" value="<?php e(__('save', 'common')); ?>" />
    <?php endif; ?>

    <input type="submit" name="submit_close" id="submit_close-select_file" value="<?php e(__('save and transfer')); ?>" />
    eller <a href="<?php e($receive_redirect->getRedirect($this->url())); ?>"><?php e(__('Cancel' ,'common')); ?></a>
</div>

</form>

<?php echo $filemanager->getDBQuery()->display('paging'); ?>