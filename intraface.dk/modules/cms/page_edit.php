<?php
require('../../include_first.php');
require_once('Intraface/tools/Position.php');

$module_cms = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

$kernel->useShared('filehandler');

if (!empty($_POST)) {

	$cmssite = new CMS_Site($kernel, $_POST['site_id']);
	$cmspage = new CMS_Page($cmssite, $_POST['id']);

		if (!empty($_FILES['new_pic'])) {
			$filehandler = new FileHandler($kernel);
			$filehandler->createUpload();
			$filehandler->upload->setSetting('file_accessibility', 'public');
			$id = $filehandler->upload->upload('new_pic');

			if($id != 0) {
				$_POST['pic_id'] = $id;
			}
		}

	if ($cmspage->save($_POST)) {
		if(!empty($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
			$redirect = Redirect::factory($kernel, 'go');
			$module_filemanager = $kernel->useModule('filemanager');
			$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_cms->getPath().'page_edit.php?id='.$cmspage->get('id'));
			$redirect->askParameter('file_handler_id');
			header('Location: '.$url);
			exit;
		}
		elseif (!empty($_POST['close'])) {
			header('Location: page.php?id='.$cmspage->get('id'));
			exit;
		}
		elseif (!empty($_POST['add_keywords'])) {
			$keyword_shared = $kernel->useShared('keyword');
			header('Location: '.$keyword_shared->getPath().'connect.php?page_id='.$cmspage->get('id'));
			exit;
		}
		else {
			header('Location: page_edit.php?id='.$cmspage->get('id'));
			exit;
		}
	}
	else {
		$value = $_POST;
		$template = & $cmspage->template;
	}
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$cmspage = CMS_Page::factory($kernel, 'id', $_GET['id']);
	/*
	if ($cmspage->isLocked()) {
		header('Location: page_locked.php?id='.rawurlencode($_GET['id']));
		exit;
	}
	*/
	if (!empty($_GET['action'])) {
		$position = new Position("cms_element", "page_id = " . $cmspage->get('id'), "position", "id");
		if (isset($_GET['action']) AND $_GET['action'] == "moveupelement"){
			$position->moveUp($_GET['element_id']);
			$position->reposition();
		}
    if (isset($_GET['action']) AND $_GET['action'] == "movedownelement"){
			$position->moveDown($_GET['element_id']);
			$position->reposition();
		}
	}
	/*
	$cmspage->lock();
	*/

	$value = $cmspage->get();
	$template = & $cmspage->template;

	// til select - denne kan uden problemer fortrydes ved blot at have et link til samme side
	if (!empty($_GET['return_redirect_id']) AND is_numeric($_GET['return_redirect_id'])) {
		$redirect = Redirect::factory($kernel, 'return');
		$value['pic_id'] = $redirect->getParameter('file_handler_id');
	}


}
elseif (!empty($_GET['site_id']) AND is_numeric($_GET['site_id'])) {
	$cmssite = new CMS_Site($kernel, $_GET['site_id']);
	$cmspage = new CMS_Page($cmssite);
	$value['site_id'] = $_GET['site_id'];
	$template = new CMS_Template($cmssite);
}
else {
	trigger_error($translation->get('not allowed', 'common'), E_USER_ERROR);
}


$templates = $template->getList();

$cmspages = $cmspage->getList();

$page = new Page($kernel);
$page->includeJavascript('module', 'page_edit.js');
$page->start(safeToHtml($translation->get('edit page')));
?>

<h1><?php echo safeToHtml($translation->get('edit page')); ?></h1>

<ul class="options">
	<li><a href="site.php?id=<?php echo intval($cmspage->cmssite->get('id')); ?>"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
	<?php if ($cmspage->get('id') > 0): ?>
	<li><a href="page.php?id=<?php echo intval($cmspage->get('id')); ?>"><?php echo safeToHtml($translation->get('view page')); ?></a></li>
	<?php endif; ?>
</ul>

<?php echo $cmspage->error->view($translation); ?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>"  enctype="multipart/form-data">
	<input name="id" type="hidden" value="<?php if (!empty($value['id'])) echo intval($value['id']); ?>" />
	<input name="site_id" type="hidden" value="<?php if (!empty($value['site_id'])) echo intval($value['site_id']); ?>" />

	<fieldset>
		<legend><?php echo safeToHtml($translation->get('about the behavior of the page')); ?></legend>
	<!-- is is not possible to change template -->
	<?php if (!empty($value['template_id'])): ?>
		<input type="hidden" name="template_id" value="<?php  if (!empty($value['template_id'])) echo intval($value['template_id']); ?>" />

	<?php elseif (is_array($templates) AND count($templates) > 1): ?>

		<div class="formrow">
			<label><?php echo safeToHtml($translation->get('choose template')); ?></label>
			<select name="template_id">
			<?php foreach ($templates AS $template): ?>
				<option value="<?php echo intval($template['id']); ?>"><?php echo safeToForm($template['name']); ?></option>
			<?php endforeach; ?>
			</select>
		</div>


	<?php else: ?>
		<input type="hidden" name="template_id" value="<?php echo intval($templates[0]['id']); ?>" />
	<?php endif; ?>

		<div class="formrow">
			<label for="page-type"><?php echo safeToHtml($translation->get('choose page type')); ?></label>
			<select name="page_type" id="cms-page-type">
				<?php foreach ($cmspage->type AS $key => $type): ?>
				<option value="<?php echo $type; ?>"<?php if (!empty($value['type']) AND $value['type'] == $type) echo ' selected="selected"' ?>><?php echo safeToForm($translation->get($type)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<fieldset>

		<legend><?php echo safeToHtml($translation->get('page information')); ?></legend>

		<div class="formrow" id="titlerow">
			<label for="title"><?php echo safeToHtml($translation->get('title')); ?></label>
			<input name="title" type="text" id="title" value="<?php if (!empty($value['title'])) echo safeToForm($value['title']); ?>" size="50" maxlength="50" />
		</div>

		<div class="formrow">
			<label for="shortlink"><?php echo safeToHtml($translation->get('url identifier')); ?></label>
			<input name="identifier" type="text" id="shortlink" value="<?php if (!empty($value['identifier'])) echo safeToForm($value['identifier']); ?>" size="50" maxlength="50" />
		</div>

	</fieldset>

	<?php if (empty($value['type']) OR $value['type'] == 'page'): ?>
	<fieldset id="cms-page-info">
		<legend><?php echo safeToHtml($translation->get('page information')); ?></legend>
		<div class="formrow">
			<label for="navigation-name"><?php echo safeToHtml($translation->get('name in the navigation')); ?></label>
			<input name="navigation_name" type="text" id="navigation-name" value="<?php if (!empty($value['navigation_name'])) echo safeToForm($value['navigation_name']); ?>" size="50" maxlength="50" />
		</div>

		<?php if (is_array($cmspages) AND count($cmspages) > 0): ?>

		<div class="formrow" id="childof">
			<label for="child_of_id"><?php echo safeToHtml($translation->get('choose page is child of')); ?></label>
			<select name="child_of_id" id="child_of_id">
				<option value="0"><?php echo safeToForm($translation->get('none', 'common')); ?></option>
				<?php
					foreach ($cmspages AS $p) {
						if (!empty($value['id']) AND $p['id'] == $value['id']) continue;
						echo '<option value="'.$p['id'].'"';
						if (!empty($value['child_of_id']) AND $value['child_of_id'] == $p['id']) echo ' selected="selected"';
						echo '>'.safeToForm($p['title']).'</option>';
					}
				?>
			</select>
		</div>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>

	<fieldset>

			<legend><?php echo safeToHtml($translation->get('choose picture')); ?></legend>
			<!--
			<?php if (!empty($_GET['selected_file_id']) AND is_numeric($_GET['selected_file_id'])) { ?>
				<p class="message"><?php echo safeToHtml($translation->get('you have chosen this picture')); ?> <input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" /> eller <a href="section_html_edit.php?id=<?php echo intval($value['id']); ?>"><?php echo safeToForm($translation->get('regret', 'common')); ?></a>.</p>
			<?php } ?>
			-->
			<?php
				if (empty($value['pic_id'])) $value['pic_id'] = 0;
				$filehandler = new FileHandler($kernel, $value['pic_id']);
				$filehandler_html = new FileHandlerHTML($filehandler);
				$filehandler_html->printFormUploadTag('pic_id', 'new_pic', 'choose_file', array('image_size' => 'small'));
			?>
		</fieldset>


	<fieldset id="searchengine-info">
		<legend><?php echo safeToHtml($translation->get('metatags for the search engines')); ?></legend>
		<!--<p>Søgeordene og beskrivelserne er ikke noget, du kan se direkte på hjemmesiden. Det skrives i <code>head</code>-sektionen af <abbr title="Hyper Text Markup Language">HTML</abbr>-siden, som nogle søgemaskiner til gengæld kigger i.</p>-->
		<p><?php echo safeToHtml($translation->get('explaining metatags for the search engines')); ?></p>
		<div class="formrow">
			<label for="description"><?php echo safeToHtml($translation->get('search engine description')); ?></label>
			<textarea name="description" id="description" cols="50" rows="3"><?php  if (!empty($value['description'])) echo safeToForm($value['description']); ?></textarea>
		</div>

		<div class="formrow">
			<label for="keywords"><?php echo safeToHtml($translation->get('search engine keywords')); ?></label>
			<input name="keywords" id="keywords" type="text" value="<?php if (!empty($value['keywords'])) echo safeToForm($value['keywords']); ?>" size="50" maxlength="225" />
		</div>
	</fieldset>

	<?php if ($kernel->intranet->hasModuleAccess('comment')): ?>
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('comments')); ?></legend>
			<div class="radiorow">
			<label><input type="checkbox" value="1" name="allow_comments"<?php if (!empty($value['allow_comments']) AND $value['allow_comments'] == 1) echo ' checked="checked"'; ?> /> <?php echo safeToHtml($translation->get('users can comment page')); ?></label>
		</div>

	</fieldset>
	<?php endif; ?>



	<fieldset id="date-settings">
		<legend><?php echo safeToHtml($translation->get('publish properties')); ?></legend>

		<div class="formrow">
			<label for="date-publish"><?php echo safeToHtml($translation->get('publish date')); ?></label>
			<input name="date_publish" id="date-publish" type="text" value="<?php if (!empty($value['date_publish'])) echo safeToForm($value['date_publish']); ?>" size="30" maxlength="225" /> <span id="dateFieldMsg1"><?php echo safeToHtml($translation->get('empty is today')); ?></span>
		</div>

		<div class="formrow">
			<label for="date-expire"><?php echo safeToHtml($translation->get('expire date')); ?></label>
			<input name="date_expire" id="date-expire" type="text" value="<?php if (!empty($value['date_expire']))  echo safeToForm($value['date_expire']); ?>" size="30" maxlength="225" /> <span id="dateFieldMsg2"><?php echo safeToHtml($translation->get('empty never expires')); ?></span>
		</div>

		<div class="radiorow">
			<label><input type="checkbox" value="1" name="hidden" <?php if (!empty($value['hidden']) AND $value['hidden'] == 1) echo ' checked="checked"'; ?> /> <?php echo safeToHtml($translation->get('hide page')); ?></label>
		</div>
		<!--
		<div class="formrow">
			<label for="password"><?php echo safeToHtml($translation->get('password', 'common')); ?></label>
			<input type="text" value="<?php if(!empty($value['password'])) echo safeToForm($value['password']); ?>" name="password" />
		</div>
		-->


	</fieldset>

	<div style="clear: both;">
		<input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
		<input type="submit" name="close" value="<?php echo safeToHtml($translation->get('save and close', 'common')); ?>" />
		<input type="submit" name="add_keywords" value="<?php echo safeToHtml($translation->get('add keywords', 'keyword')); ?>" />
	</div>
</form>

<?php
$page->end();
?>