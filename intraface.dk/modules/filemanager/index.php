<?php
require '../../include_first.php';

$module = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $filemanager = new FileManager($kernel, $_GET['delete']);
    if (!$filemanager->delete()) {
        trigger_error($translation->get('could not delete file'), E_USER_ERROR);
    }
} elseif (!empty($_GET['undelete']) AND is_numeric($_GET['undelete'])) {
    $filemanager = new FileManager($kernel, $_GET['undelete']);
    if (!$filemanager->undelete()) {
        trigger_error($translation->get('could not undelete file'), E_USER_ERROR);
    }
} else {
    $filemanager = new FileManager($kernel);

}

/*
if(isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0 && $kernel->user->hasModuleAccess('contact')) {
    $contact_module = $kernel->useModule('contact');
    $contact = new Contact($kernel, $_GET['contact_id']);
    $procurement->getDBQuery()->setFilter("contact_id", $_GET["contact_id"]);
}
*/

if(isset($_GET["search"])) {

    if(isset($_GET["text"]) && $_GET["text"] != "") {
        $filemanager->getDBQuery()->setFilter("text", $_GET["text"]);
    }

    if(isset($_GET["filtration"]) && intval($_GET["filtration"]) != 0) {
        // Kun for at filtration igen vises i søgeboksen
        $filemanager->getDBQuery()->setFilter("filtration", $_GET["filtration"]);

        switch($_GET["filtration"]) {
            case 1:
                $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y")." 00:00");
                break;
            case 2:
                $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y", time()-60*60*24)." 00:00");
                $filemanager->getDBQuery()->setFilter("uploaded_to_date", date("d-m-Y", time()-60*60*24)." 23:59");
                break;
            case 3:
                $filemanager->getDBQuery()->setFilter("uploaded_from_date", date("d-m-Y", time()-60*60*24*7)." 00:00");
                break;
            case 4:
                $filemanager->getDBQuery()->setFilter("edited_from_date", date("d-m-Y")." 00:00");
                break;
            case 5:
                $filemanager->getDBQuery()->setFilter("edited_from_date", date("d-m-Y", time()-60*60*24)." 00:00");
                $filemanager->getDBQuery()->setFilter("edited_to_date", date("d-m-Y", time()-60*60*24)." 23:59");
                break;
            case 6:
                $filemanager->getDBQuery()->setFilter('accessibility', 'public');
                break;
            case 7:
                $filemanager->getDBQuery()->setFilter('accessibility', 'intranet');
                break;
            default:
                // Probaly 0, so nothing happens
        }
    }

    if(isset($_GET['keyword']) && is_array($_GET['keyword']) && count($_GET['keyword']) > 0) {

        $filemanager->getDBQuery()->setKeyword($_GET['keyword']);
    }
}
elseif(isset($_GET['character'])) {
    $filemanager->getDBQuery()->useCharacter();
}
else {
    $filemanager->getDBQuery()->setSorting('file_handler.date_created DESC');
}

$filemanager->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
$filemanager->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$filemanager->getDBQuery()->storeResult("use_stored", "filemanager", "toplevel");
// $filemanager->getDBQuery()->setExtraUri('&amp;type=1');


$files = $filemanager->getList();

$page = new Intraface_Page($kernel);
$page->start(safeToHtml($translation->get('file manager')));
?>

<h1><?php echo safeToHtml($translation->get('file manager')); ?></h1>

<?php
/*
 * Prepared to use Limiter!    
if($kernel->intranet->hasModuleAccess('ModulePackage')) {
	require_once 'Intraface/modules/modulepackage/Limiter.php';
    $limiter = new ModulePackage_Limiter();
}
*/
?>

<ul class="options">
    <li><a href="upload.php" onclick="location.href='upload_multiple.php'; return false;"><?php echo safeToHtml($translation->get('upload file')); ?></a></li>
    <?php if (count($files) > 0): ?>
        <li><a href="edit_batch.php?use_stored=true"><?php echo safeToHtml($translation->get('batch edit files')); ?></a></li>
    <?php endif; ?>
</ul>


<?php if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])): ?>
    <p class="message">Filen er slettet. <a href="<?php echo $_SERVER['PHP_SELF']; ?>?undelete=<?php echo (int)$_GET['delete']; ?>">Fortryd</a></p>
<?php endif; ?>


<?php if (!$filemanager->isFilledIn()): ?>
    <p><?php echo safeToHtml($translation->get('no files uploaded')); ?></p>
<?php else: ?>


    <form method="get" action="index.php">
        <fieldset>
            <legend><?php echo safeToHtml($translation->get('search')); ?></legend>
            <label><?php echo safeToHtml($translation->get('search text')); ?>:
                <input type="text" name="text" value="<?php echo $filemanager->getDBQuery()->getFilter("text"); ?>" />
            </label>
            <label><?php echo safeToHtml($translation->get('search filter')); ?>:
            <select name="filtration">
                <option value="0"><?php echo safeToHtml($translation->get('all', 'filehandler')); ?></option>
                <option value="1"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 1) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('uploaded today', 'filehandler')); ?></option>
                <option value="2"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 2) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('uploaded yesterday', 'filehandler')); ?></option>
                <option value="3"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 3) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('uploaded this week', 'filehandler')); ?></option>
                <option value="4"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 4) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('edited today', 'filehandler')); ?></option>
                <option value="5"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 5) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('edited yesterday', 'filehandler')); ?></option>
                <option value="6"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 6) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('public accessible', 'filemanager')); ?></option>
                <option value="7"<?php if ($filemanager->getDBQuery()->getFilter("filtration") == 7) echo ' selected="selected"';?>><?php echo safeToHtml($translation->get('only accessible from intranet', 'filemanager')); ?></option>
    
            </select>
            </label>
            <span>
            <input type="submit" name="search" value="<?php echo safeToHtml($translation->get('search')); ?>" />
            </span>
    
            <?php
    
            $selected_keywords = $filemanager->getDBQuery()->getKeyword();
    
        $keyword = $filemanager->getKeywordAppender();
        $keywords = $keyword->getUsedKeywords();
    
        if(count($keywords) > 0) {
            echo '<div>'. safeToHtml($translation->get('keywords', 'keyword')) . ': <ul style="display: inline;">';
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
    
    
    
    <table class="stripe">
        <caption><?php echo safeToHtml($translation->get('files')); ?></caption>
        <thead>
            <tr>
                <th></th>
                <th><?php echo safeToHtml($translation->get('file name')); ?></th>
                <th><?php echo safeToHtml($translation->get('file type')); ?></th>
                <th><?php echo safeToHtml($translation->get('file accessibility')); ?></th>
                <th><?php echo safeToHtml($translation->get('file size')); ?></th>
                <th><?php echo safeToHtml($translation->get('file date')); ?></th>
                <th></th>
            </tr>
        </thead>
    
        <tbody>
            <?php
    
    
    
            for($i = 0, $max = count($files); $i < $max; $i++) {
                ?>
                <tr>
                    <td style="height: 67px;"><a href="<?php print($files[$i]['file_uri']); ?>" target="_blank"><img src="<?php print($files[$i]["icon_uri"]); ?>" style="height: <?php echo safeToHtml($files[$i]["icon_height"]); ?>px; width: <?php echo safeToHtml($files[$i]["icon_width"]); ?>px;" /></a></td>
                    <td><a href="file.php?id=<?php print($files[$i]["id"]); ?>"><?php echo safeToHtml($files[$i]["file_name"]); ?></a>
                        <br /><i><?php echo safeToHtml(substr(strip_tags($files[$i]["description"]), 0, 100)); if(strlen(strip_tags($files[$i]["description"])) > 100) print('...'); ?></i>
                    </td>
                    <td style="white-space: nowrap;"><?php echo safeToHtml($files[$i]["file_type"]['description']); ?></td>
                    <td style="white-space: nowrap;"><?php echo safeToHtml($translation->get($files[$i]["accessibility"])); ?></td>
                    <td style="white-space: nowrap;"><?php echo safeToHtml($files[$i]["dk_file_size"]); ?></td>
                    <td style="white-space: nowrap;"><?php echo safeToHtml($files[$i]["dk_date_created"]); ?></td>
                    <td style="width: 120px;" class="options">
                        <a class="edit" href="edit.php?id=<?php echo $files[$i]['id']; ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a>
                        <a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $files[$i]['id']; ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    
    <?php echo $filemanager->getDBQuery()->display('paging'); ?>

<?php endif; ?>
<?php
$page->end();
?>
