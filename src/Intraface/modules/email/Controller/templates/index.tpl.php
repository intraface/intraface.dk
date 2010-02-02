<h1><?php e(t('e-mails')); ?></h1>

<?php if (count($emails) == 0): ?>

	<p><?php e(t('no e-mails has been sent')); ?></p>

<?php else: ?>

	<?php
		//til test af sentThisHour(). B�r ikke vises n�r vi er i produktion.
		//echo $email_object->sentThisHour();
	?>

	<?php if ($queue > 0): ?>
		<p><?php e(t('e-mails are in queue - the will be sent soon')); ?></p>
	<?php endif; ?>

	<?php echo $gateway->getDBQuery()->display('character'); ?>

	<table>
	<caption><?php e(t('e-mails')); ?></caption>
	<thead>
	<tr>
		<th><?php e(t('subject')); ?></th>
		<th><?php e(t('contact')); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($emails AS $email): ?>
	<tr>
		<td><a href="<?php e(url($email['id'])); ?>"><?php e($email['subject']); ?></a></td>
		<td><a href="<?php e($contact_module->getPath()); ?><?php e($email['contact_id']); ?>"><?php e($email['contact_name']); ?></a></td>
		<td>
		<?php if (!empty($email['status']) AND $email['status'] != 'sent'): ?>
			<a class="edit" href="<?php e(url($email['id'], array('edit'))); ?>"><?php e(t('edit')); ?></a>
			<a class="delete" href="<?php e(url(null, array('delete' => $email['id']))); ?>"><?php e(t('delete')); ?></a>
		<?php else: ?>
			<?php e(t($email['status'])); ?>
		<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<?php echo $gateway->getDBQuery()->display('paging'); ?>

<?php endif; ?>
