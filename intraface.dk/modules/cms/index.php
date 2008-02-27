<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$site = new CMS_Site($kernel, $_GET['delete']);
	if (!$site->delete()) {
		trigger_error($translation->get('the site could not be deleted'), E_USER_ERROR);
	}
}

$site = new CMS_Site($kernel);
$sites = $site->getList();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('cms')));
?>

<h1><?php echo safeToHtml($translation->get('cms')); ?></h1>

<ul class="options">
	<li><a href="site_edit.php"><?php echo safeToHtml($translation->get('create site')); ?></a></li>
</ul>

<?php if (is_array($sites) AND count($sites) == 0): ?>
	<p><?php echo safeToHtml($translation->get('no sites created')); ?></p>
<?php else: ?>
<table>
<caption><?php echo safeToHtml($translation->get('sites')); ?></caption>
<thead>
	<tr>
		<th><?php echo safeToHtml($translation->get('name')); ?></th>
		<th><?php e(t('go directly to')); ?></th>
        <th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($sites AS $s): ?>
<tr>
	<td><a href="site.php?id=<?php echo intval($s['id']); ?>"><?php echo safeToHtml($s['name']);  ?></a></td>
	<td><a href="pages.php?type=page&amp;id=<?php echo intval($s['id']); ?>"><?php e(t('pages'));  ?></a>, <a href="pages.php?type=article&amp;id=<?php echo intval($s['id']); ?>"><?php e(t('articles'));  ?></a>, <a href="pages.php?type=news&amp;id=<?php echo intval($s['id']); ?>"><?php e(t('news'));  ?></a></td>
    <td class="options">
		<a class="edit" href="site_edit.php?id=<?php echo intval($s['id']); ?>"><?php echo safeToHtmL($translation->get('edit settings', 'common')); ?></a>
		<a class="delete" href="index.php?delete=<?php echo intval($s['id']); ?>"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>
