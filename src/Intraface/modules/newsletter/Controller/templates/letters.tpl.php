<h1>Breve <?php e($context->getList()->get('title')); ?></h1>

<ul class="options">
	<li><a class="new" href="<?php e(url(null, array('new'))); ?>">Opret brev</a></li>
	<li><a href="<?php e(url('../')); ?>">Tilbage til liste</a></li>

</ul>

<?php echo $context->getLetter()->error->view(); ?>

<?php if (count($context->getLetters()) == 0): ?>
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
	<?php foreach ($context->getLetters() AS $letter): ?>
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
				<a href="<?php e(url($letter['id'] . '/send')); ?>">Send</a>
				<a class="edit" href="<?php e(url($letter['id'], array('edit'))); ?>">Ret</a>
				<a class="delete" href="<?php e(url($letter['id'], array('delete'))); ?>&amp;delete=<?php e($letter['id']); ?>" title="Dette sletter nyhedsbrevet">Slet</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
