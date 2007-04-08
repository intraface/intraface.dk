<?php
require('../../include_first.php');

$module = $kernel->module("newsletter");


if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$list = new NewsletterList($kernel, $_GET['delete']);
	$list->delete();
}
else {
	$list = new NewsletterList($kernel);
}

$lists = $list->getList();

$page = new Page($kernel);
$page->start('Nyhedsbrevslister');
?>

<h1>Lister</h1>

<ul class="options">
	<li><a class="new" href="list_edit.php">Opret liste</a></li>
</ul>

<?php if (is_object($list->error)) $list->error->view(); ?>

<?php if (count($lists) == 0): ?>
<p>Der er ikke oprettet nogen lister.</p>
<?php else: ?>

<table class="stripe">
	<caption>E-mail-lister</caption>
	<thead>
	<tr>
		<th>Navn</th>
		<th>Modtagere</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($lists AS $list): ?>
	<tr <?php if (!empty($_GET['from_id']) AND $_GET['from_id'] == $list['id']) echo 'id="list" class="fade"'; ?>>
		<td><a href="list.php?id=<?php echo intval($list['id']); ?>"><?php echo safeToForm($list['title']); ?></a></td>
		<td><?php echo intval($list['subscribers']); ?></td>
		<td class="options">
			<a class="edit" href="list_edit.php?id=<?php echo intval($list['id']); ?>">Ret</a>
			<a class="delete" href="index.php?delete=<?php echo intval($list['id']); ?>&amp;id=<?php echo intval($list['id']); ?>">Slet</a>
		</td>

	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
<?php
$page->end();
?>