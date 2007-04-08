<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$template = CMS_Template::factory($kernel, 'id', $_GET['delete']);
	$template->delete();
	$cmssite = & $template->cmssite;
}
elseif (!empty($_GET['site_id']) AND is_numeric($_GET['site_id'])) {

	$cmssite = new CMS_Site($kernel, $_GET['site_id']);
	$template = new CMS_Template($cmssite);
}
else {
	trigger_error('site id has to be set', E_USER_ERROR);
}

$templates = $template->getList();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('templates')));
?>

<h1><?php echo safeToHtml($translation->get('templates')); ?></h1>

<ul class="options">
	<li><a class="new" href="template_edit.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('create template')); ?></a></li>
	<li><a href="site.php?id=<?php echo $cmssite->get('id'); ?>"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
</ul>

<?php if (count($templates) == 0): ?>
	<p><?php echo safeToHtml($translation->get('no templates found')); ?></p>
<?php else: ?>
<table>
<caption><?php echo safeToHtml($translation->get('templates')); ?></caption>
<thead>
<tr>
	<th><?php echo safeToHtml($translation->get('template name')); ?></th>
	<th><?php echo safeToHtml($translation->get('identifier', 'common')); ?></th>
	<th></th>
</tr>
</thead>
<tbody>
<?php foreach ($templates AS $s): ?>
	<tr>
		<td><a href="template.php?id=<?php echo $s['id']; ?>"><?php echo safeToHtml($s['name']);  ?></a></td>
		<td><?php echo safeToHtml($s['identifier']); ?></td>
		<td class="options">
			<a class="edit" href="template_edit.php?id=<?php echo $s['id']; ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a>
			<a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $s['id']; ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>
