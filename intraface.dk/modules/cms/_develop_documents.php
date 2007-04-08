<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_POST['page'])) {

	foreach ($_POST['page'] AS $key=>$value) {

		$cmssite = new CMS_Site($kernel, $_POST['id']);
		$cmspage = new CMS_Page($cmssite, $_POST['page'][$key]);
		if ($cmspage->setStatus($_POST['status'][$key])) {
		}

	}


	if (isAjax()) {
		echo 1;
		exit;
	}
	else {
		header('Location: site.php?id='.$cmssite->get('id'));
		exit;
	}

}


if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$cmspage = CMS_Page::factory($kernel, 'id', $_GET['delete']);
	$cmspage->delete();
	$cmssite = & $cmspage->cmssite;
}
else {
	$cmssite = new CMS_Site($kernel, (int)$_GET['id']);
}


$cmspage = new CMS_Page($cmssite);
$articles = $cmspage->getList('article');
$news = $cmspage->getList('news');

$page = new Page($kernel);
$page->includeJavascript('global', 'yui/connection/connection-min.js');
$page->includeJavascript('global', 'checkboxes.js');
$page->includeJavascript('module', 'publish.js');
$page->start(safeToHtml($translation->get('documents')));
?>

<h1><?php echo safeToHtml($translation->get('documents')) . ' ' . safeToHtml($translation->get('on', 'common')) . ' ' . safeToHtml($cmssite->get('name')); ?></h1>

<?php if (count($cmspage->template->getList()) == 0): ?>

	<p class="message-dependent">
		<?php echo safeToHtml($translation->get('you have to create templates')); ?><!--Du kan kan ikke oprette sider, før du har oprettet nogle skabeloner.-->
		<?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
			<a href="template_edit.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('create template'));?></a>.
		<?php else: ?>
			<strong><?php echo safeToHtml($translation->get('you cannot create templates')); ?><!--Desværre har du ikke ret til at oprette skabeloner.--></strong>
		<?php endif; ?>
	</p>

<?php else: ?>
<ul class="options">
	<li><a class="new" href="page_edit.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('create page')); ?></a></li>
	<li><a class="edit" href="site_edit.php?id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('edit site')); ?></a></li>

	<?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
	<li><a class="template" href="templates.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('templates'));?></a></li>
	<?php endif; ?>
	<?php if ($kernel->user->hasSubAccess('cms', 'edit_stylesheet')): ?>
	<li><a class="stylesheet" href="stylesheet_edit.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('stylesheet')); ?></a></li>
	<?php endif; ?>
</ul>


<form id="form-site" action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<input type="hidden" id="site" name="id" value="<?php echo $cmssite->get('id'); ?>" />

<?php if (is_array($articles) AND count($articles) > 0): ?>
<table>
	<thead>
		<tr>
			<th><?php echo safeToHtml($translation->get('title')); ?></th>
			<th><?php echo safeToHtml($translation->get('identifier', 'common')); ?></th>
			<th><?php echo safeToHtml($translation->get('published', 'common')); ?></th>
			<th><?php echo safeToHtml($translation->get('show', 'common')); ?></th>
			<th colspan="2"></th>
		</tr>
	</thead>

<caption><?php echo safeToHtml($translation->get('articles')); ?></caption>
<?php foreach ($articles AS $p):?>
	<tr>
		<td><a href="page.php?id=<?php echo $p['id']; ?>"><?php echo safeToHtml($p['title']); ?></a></td>
		<td><?php echo $p['identifier']; ?></td>
		<td>
			<input type="hidden" name="page[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
			<input class="input-publish" id="<?php echo $p['id']; ?>" type="checkbox" name="status[<?php echo $p['id']; ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
		</td>
		<td>
		<?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der bør laves et eller andet, så det er muligt anyways - fx en hemmelig kode på siden ?>
			<a href="<?php echo $p['url']; ?>" target="_blank"><?php echo safeToHtml($translation->get('show page')); ?></a>
		<?php endif; ?>
		</td>
		<td class="options"><a class="edit" href="page_edit.php?id=<?php echo $p['id']; ?>"><?php echo safeToHtml($translation->get('edit')); ?></a>
		<a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $p['id']; ?>"><?php echo safeToHtml($translation->get('delete')); ?></a></td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (is_array($news) AND count($news) > 0): ?>
<table>
<caption><?php echo safeToHtml($translation->get('news')); ?></caption>
	<thead>
		<tr>
			<th><?php echo safeToHtml($translation->get('date', 'common')); ?></th>
			<th><?php echo safeToHtml($translation->get('title')); ?></th>
			<th><?php echo safeToHtml($translation->get('identifier', 'common')); ?></th>
			<th><?php echo safeToHtml($translation->get('published', 'common')); ?></th>
			<th><?php echo safeToHtml($translation->get('show', 'common')); ?></th>
			<th colspan="2"></th>
		</tr>
	</thead>
<?php foreach ($news AS $p):?>
	<tr>
		<td><?php echo safeToHtml($p['date_publish_dk']); ?></td>
		<td><a href="page.php?id=<?php echo $p['id']; ?>"><?php echo $p['title']; ?></a></td>
		<td><?php echo $p['identifier']; ?></td>
		<td>
			<input type="hidden" name="page[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
			<input class="input-publish" id="<?php echo $p['id']; ?>" type="checkbox" name="status[<?php echo $p['id']; ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
		</td>
		<td>
		<?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der bør laves et eller andet, så det er muligt anyways - fx en hemmelig kode på siden ?>
			<a href="<?php echo $p['url']; ?>" target="_blank"><?php echo safeToHtml($translation->get('show page')); ?></a>
		<?php endif; ?>
		</td>

		<td class="options"><a class="edit" href="page_edit.php?id=<?php echo $p['id']; ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a>
		<a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $p['id']; ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a></td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<p><input type="submit" value="Gem" id="submit-publish" /></p>
<?php endif; ?>



</form>


<?php
$page->end();
?>