<h1><?php e(t('Memos')); ?></h1>


	<ul class="options">
		<li><a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Add memo')); ?></a></li>
	</ul>

<table>
	<caption><?php e(t('Memos')); ?></caption>
<?php foreach ($memos as $memo): ?>
	<tr>
		<td><?php e($memo['dk_reminder_date']); ?></td>
		<td><a href="<?php e(url($memo['id'])); ?>"><?php e($memo['subject']); ?></a></td>
		<td><a href="<?php e(url('../../' . $memo['contact_id'])); ?>"><?php e(t('Contact')); ?> <?php e($memo['contact_id']); ?></a></td>
	</tr>
<?php endforeach; ?>
</table>