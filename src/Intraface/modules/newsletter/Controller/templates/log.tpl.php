<?php
$logs = $context->getLog();
?>

<h1><?php e(__('Log')); ?></h1>

<table class="stripe">
	<caption><?php e(__('Unsubscribed')); ?></caption>
	<thead>
	<tr>
		<th><?php e(__('Date')); ?></th>
		<th><?php e(__('Contact #id')); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php while ($logs->nextRecord()): ?>
	<tr>
		<td><?php e($logs->f('dk_date_unsubscribe')); ?></td>
		<td><?php e($logs->f('contact_id')); ?></td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>
