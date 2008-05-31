<?php
require('../../include_first.php');

$module = $kernel->module("newsletter");

if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$list = new NewsletterList($kernel, $_GET['delete']);
	$list->delete();
} else {
	$list = new NewsletterList($kernel);
}

$lists = $list->getList();

$page = new Intraface_Page($kernel);
$page->start('Nyhedsbrevslister');
?>

<h1>Lister</h1>

<ul class="options">
	<li><a class="new" href="list_edit.php">Opret liste</a></li>
</ul>

<?php if (is_object($list->error)) echo $list->error->view(); ?>

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
	<tr>
		<td><a href="list.php?id=<?php e($list['id']); ?>"><?php e($list['title']); ?></a></td>
		<td><?php echo intval($list['subscribers']); ?></td>
		<td class="options">
			<a class="edit" href="list_edit.php?id=<?php e($list['id']); ?>">Ret</a>
			<a class="delete" href="index.php?delete=<?php e($list['id']); ?>&amp;id=<?php e($list['id']); ?>">Slet</a>
		</td>

	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>
<?php
$page->end();
?>