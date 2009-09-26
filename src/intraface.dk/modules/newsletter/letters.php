<?php
require('../../include_first.php');
if (empty($_GET['list_id']) OR !is_numeric($_GET['list_id'])) {
	trigger_error('Siden kræver en liste id', E_USER_ERROR);
	exit;
}

$kernel->module('newsletter');
$translation = $kernel->getTranslation('newsletter');

$list = new NewsletterList($kernel, (int)$_GET['list_id']);

if (!empty($_GET['delete']) AND is_numeric($_GET['list_id'])) {
	$letter = new Newsletter($list, $_GET['delete']);
	$letter->delete();
}

$letter = new Newsletter($list);
$letters = $letter->getList();

$page = new Intraface_Page($kernel);
$page->start('Breve');
?>

<h1>Breve <?php e($list->get('title')); ?></h1>

<ul class="options">
	<li><a class="new" href="letter_edit.php?list_id=<?php e($list->get('id')); ?>">Opret brev</a></li>
	<li><a href="list.php?id=<?php e($list->get('id')); ?>">Tilbage til liste</a></li>

</ul>

<?php echo $letter->error->view(); ?>

<?php if (count($letters) == 0): ?>
	<p>Der er ikke oprettet nogen breve endnu.</p>
<?php else: ?>
<table class="stripe">
	<caption>Breve</caption>
	<thead>
	<tr>
		<th>Titel</th>
		<th>Status</th>
		<th>Modtagere</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($letters AS $letter): ?>
	<tr>
		<td><a href="letter.php?id=<?php e($letter['id']); ?>"><?php e($letter['subject']); ?></a></td>
		<td><?php e(__($letter['status'])); ?></td>
		<td>
			<?php
				if ($letter['status'] == 'sent'):
					e($letter['sent_to_receivers']);
				else:
					e('Ikke sendt endnu');
				endif;
			?>
		</td>
		<td class="buttons">
			<?php if ($letter['status'] != 'sent'): ?>
				<a href="send.php?id=<?php e($letter['id']); ?>">Send</a>
				<a class="edit" href="letter_edit.php?id=<?php e($letter['id']); ?>">Ret</a>
				<a class="delete" href="letters.php?list_id=<?php e($list->get('id')); ?>&amp;delete=<?php e($letter['id']); ?>" title="Dette sletter nyhedsbrevet">Slet</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php
$page->end();
?>