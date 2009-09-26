<?php
require '../../include_first.php';

$module_filemanager = $kernel->module('filemanager');
$translation = $kernel->getTranslation('filemanager');

// Dette bør laves så det virker både med og uden ajax
// Du skal først spørge om det er ajax, når du tjekker om der er success
// hvis det er ajax outputter den 1 eller 0.
// hvis det ikke er ajax går den bare videre.

if (isset($_POST['ajax'])) {

	if (!isset($_POST['redirect_id'])) {
		e('0');
	}

	// print($_SERVER['REQUEST_URI']);
	// exit;
	$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
    $redirect = new Ilib_Redirect($kernel->getSessionId(), MDB2::facotory(DB_DSN), intval($_POST['redirect_id']), $options);
	if (isset($_POST['add_file_id'])) {
		$filemanager = new Filemanager($kernel, intval($_POST['add_file_id']));
		if ($filemanager->get('id') != 0) {
			$redirect->setParameter("file_handler_id", $filemanager->get('id'));
			e('1');
			exit;
		}
	}
	if (isset($_POST['remove_file_id'])) {
		$redirect->removeParameter('file_handler_id', (int)$_POST['remove_file_id']);
		e('1');
		exit;
	}
	e('0');
	exit;
}


$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
$receive_redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::singleton(DB_DSN), 'receive', $options);
if ($receive_redirect->isMultipleParameter('file_handler_id')) {
	$multiple_choice = true;
} else {
	$multiple_choice = false;
}

if (isset($_POST['return'])) {
	// Return is when AJAX is active, and then the checked files is already saved and should not be saved again.

	header("Location: ".$receive_redirect->getRedirect('index.php'));
	exit;
}

$filemanager = new FileManager($kernel); // has to be loaded here, while it should be able to set an error just below.

if (isset($_POST['submit_close']) || isset($_POST['submit'])) {
	settype($_POST['selected'], 'array');
	$selected = $_POST['selected'];

	$number_of_files = 0;
	foreach ($selected AS $id) {
		$tmp_f = new Filemanager($kernel, (int)$id);
		if ($tmp_f->get('id') != 0) {
			$receive_redirect->setParameter("file_handler_id", $tmp_f->get('id'));
			$number_of_files++;
		}

	}

	if ($number_of_files == 0) {
		$filemanager->error->set("you have to choose a file");
	} elseif ($multiple_choice == false || isset($_POST['submit_close'])) {
		header("Location: ".$receive_redirect->getRedirect('index.php'));
		exit;
	}
}

if (isset($_GET['upload'])) {
	$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
    $upload_redirect = Ilib_Redirect::factory($kernel->getSessionId(), MDB2::singleton(DB_DSN), 'go', $options);

	if ($_GET['upload'] == 'multiple') {
		$url = $upload_redirect->setDestination($module_filemanager->getPath().'upload_multiple.php', $module_filemanager->getPath().'select_file.php?redirect_id='.$receive_redirect->get('id').'&filtration=1');
	} else {
		$url = $upload_redirect->setDestination($module_filemanager->getPath().'upload.php', $module_filemanager->getPath().'select_file.php?redirect_id='.$receive_redirect->get('id').'&filtration=1');
	}
	header("Location: ".$url);
}

if ($multiple_choice) {
	$selected_files = $receive_redirect->getParameter('file_handler_id');
} else {
	if (isset($_GET['selected_file_id'])) {
		$selected_files[] = (int)$_GET['selected_file_id'];
	} else {
		$selected_files = array();
	}
}

if (isset($_GET['images'])) {
	$filemanager->getDBQuery()->setFilter('images', 1);
}

if (isset($_GET["text"]) && $_GET["text"] != "") {
	$filemanager->getDBQuery()->setFilter("text", $_GET["text"]);
}

if (isset($_GET["filtration"]) && intval($_GET["filtration"]) != 0) {
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
		default:
			// Probaly 0, so nothing happens
	}
}
if (isset($_GET['keyword']) && is_array($_GET['keyword']) && count($_GET['keyword']) > 0) {
	$filemanager->getDBQuery()->setKeyword($_GET['keyword']);
}

if (isset($_GET['character'])) {
	$filemanager->getDBQuery()->useCharacter();
}

if (!isset($_GET['search'])) {
	$filemanager->getDBQuery()->setSorting('file_handler.date_created DESC');
}

$filemanager->getDBQuery()->defineCharacter('character', 'file_handler.file_name');
$filemanager->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$filemanager->getDBQuery()->storeResult("use_stored", "filemanager_select", "sublevel");
// $filemanager->getDBQuery()->setExtraUri('&amp;type=1');


$files = $filemanager->getList();

$page = new Intraface_Page($kernel);
if ($multiple_choice) {
	// Kun hvis man skal kunne vælge flere er der behov for javascript
	$page->includeJavascript('module', 'select_file.js');
}
$page->includeJavascript('global', 'yui/connection/connection-min.js');
$page->start(__('files'));
?>

<h1><?php e(__('files')); ?></h1>

<ul class="options">
	<li><a href="select_file.php?upload=single" onclick="location.href='select_file.php?upload=multiple'; return false;"><?php e(__('upload file')); ?></a></li>
</ul>

<?php echo $filemanager->error->view('html'); ?>

<form method="get" action="select_file.php?use_stored=true">
	<fieldset>
		<legend><?php e(__('search')); ?></legend>
		<label><?php e(__('text')); ?>:
			<input type="text" name="text" value="<?php e($filemanager->getDBQuery()->getFilter("text")); ?>" />
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
			<input type="checkbox" name="images" value="1" <?php if ($filemanager->getDBQuery()->getFilter("images") == 1) echo 'checked="checked"'; ?> />
		</label>
		<span>
		<input type="submit" name="search" value="<?php e(__('find')); ?>" />
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

<?php echo $filemanager->getDBQuery()->display('character'); ?>
<form method="POST" action="select_file.php">
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

		for ($i = 0, $max = count($files); $i < $max; $i++) {
			?>
			<tr>
				<td>
					<input type="<?php if ($multiple_choice): print('checkbox'); else: print('radio'); endif; ?>" value="<?php e($files[$i]["id"]); ?>" id="<?php e($files[$i]["id"]); ?>" class="input-select_file" name="selected[]" <?php if (in_array($files[$i]['id'], $selected_files)) print("checked=\"checked\""); ?> />
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

	<?php if ($multiple_choice): ?>
		<input type="submit" name="submit" id="submit-select_file" value="<?php e(__('save', 'common')); ?>" />
	<?php endif; ?>

	<input type="submit" name="submit_close" id="submit_close-select_file" value="<?php e(__('save and transfer')); ?>" />
	eller <a href="<?php e($receive_redirect->getRedirect("index.php")); ?>"><?php e(__('Cancel' ,'common')); ?></a>
</div>

</form>


<?php echo $filemanager->getDBQuery()->display('paging'); ?>


<?php
$page->end();
?>
