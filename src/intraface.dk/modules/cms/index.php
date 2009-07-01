<?php
require '../../include_first.php';

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

$page = new Intraface_Page($kernel);
$page->start($translation->get('cms'));
?>

<h1><?php e($translation->get('cms')); ?></h1>

<ul class="options">
	<li><a href="site_edit.php"><?php e($translation->get('create site')); ?></a></li>
</ul>

<?php if (is_array($sites) AND count($sites) == 0): ?>
	<p><?php e($translation->get('no sites created')); ?></p>
<?php else: ?>
<table>
<caption><?php e($translation->get('sites')); ?></caption>
<thead>
	<tr>
		<th><?php e($translation->get('name')); ?></th>
		<th><?php e(t('go directly to')); ?></th>
        <th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($sites AS $s): ?>
<tr>
	<td><a href="site.php?id=<?php e($s['id']); ?>"><?php e($s['name']);  ?></a></td>
	<td><a href="pages.php?type=page&amp;id=<?php e($s['id']); ?>"><?php e(t('pages'));  ?></a>, <a href="pages.php?type=article&amp;id=<?php e($s['id']); ?>"><?php e(t('articles'));  ?></a>, <a href="pages.php?type=news&amp;id=<?php e($s['id']); ?>"><?php e(t('news'));  ?></a></td>
    <td class="options">
		<a class="edit" href="site_edit.php?id=<?php e($s['id']); ?>"><?php e($translation->get('edit settings', 'common')); ?></a>
		<a class="delete" href="index.php?delete=<?php e($s['id']); ?>"><?php e($translation->get('delete', 'common')); ?></a>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>
