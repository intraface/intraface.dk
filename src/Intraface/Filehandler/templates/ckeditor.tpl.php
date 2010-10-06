<h1><?php e(t('Choose file')); ?></h1>

<ul class="options">
    <li><a onclick="window.close();" href="#"><?php e(t('Close')); ?></a></li>
    <li><a href="<?php e(url('upload')); ?>" onclick="location.href='<?php e(url('uploadmultiple')); ?>'; return false;"><?php e(t('Upload file')); ?></a></li>
</ul>

<?php // echo $filemanager->error->view('html'); ?>

<form method="get" action="<?php e(url(null, array('use_stored' => true))); ?>">
    <fieldset>
        <legend><?php e(t('Search')); ?></legend>
        <label><?php e(t('Text')); ?>:
            <input type="text" name="text" value="<?php e($filemanager->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label><?php e(t('Filtration')); ?>
        <select name="filtration">
            <option value="0"><?php e(t('All')); ?></option>
            <option value="1"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 1) echo ' selected="selected"';?>><?php e(t('uploaded today')); ?></option>
            <option value="2"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 2) echo ' selected="selected"';?>><?php e(t('uploaded yesterday')); ?></option>
            <option value="3"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 3) echo ' selected="selected"';?>><?php e(t('uploaded this week')); ?></option>
            <option value="4"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 4) echo ' selected="selected"';?>><?php e(t('edited today')); ?></option>
            <option value="5"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 5) echo ' selected="selected"';?>><?php e(t('edited yesterday')); ?></option>
        </select>
        </label>
        <label><?php e(t('only pictures')); ?>:
            <input type="checkbox" name="images" value="1" <?php if ($filemanager->getDBQuery()->getFilter("images") == 1) echo 'checked="checked"'; ?> />
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(t('Find')); ?>" />
        </span>

        <?php

        $selected_keywords = $filemanager->getDBQuery()->getKeyword();
    $keyword = $filemanager->getKeywordAppender();
    $keywords = $keyword->getUsedKeywords();

    if (count($keywords) > 0) {
        echo '<div>Nøgleord: <ul style="display: inline;">';
      foreach ($keywords AS $value) {
            if (in_array($value['id'], $selected_keywords) === true) {
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


			<script type="text/javascript">
			//<![CDATA[
// Helper function to get parameters from the query string.
function getUrlParam(paramName)
{
  var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
  var match = window.location.search.match(reParam) ;

  return (match && match.length > 1) ? match[1] : '' ;
}
var funcNum = getUrlParam('CKEditorFuncNum');

			//]]>
			</script>

<?php echo $filemanager->getDBQuery()->display('character'); ?>

<table class="stripe">
    <caption><?php e(t('Files')); ?></caption>
    <thead>
        <tr>
            <th></th>
            <th><?php e(t('File name')); ?></th>
            <th><?php e(t('File type')); ?></th>
            <th><?php e(t('Accessibility')); ?></th>
            <th><?php e(t('File size')); ?></th>
            <th><?php e(t('File date')); ?></th>
            <!--<th></th>-->
        </tr>
    </thead>

<?php
// @todo --> make all the instances available fore each picture
?>
    <tbody>
        <?php foreach ($files as $file) { ?>
            <tr>
                <td style="height: 67px;"><img onclick="window.opener.CKEDITOR.tools.callFunction(funcNum, this.src);" src="<?php e($file["icon_uri"]); ?>" style="height: <?php e($file["icon_height"]); ?>px; width: <?php e($file["icon_width"]); ?>px;" /></td>
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

<p>
    <a href="#" onclick="window.close();"><?php e(t('Cancel')); ?></a>
</p>


<?php echo $filemanager->getDBQuery()->display('paging'); ?>