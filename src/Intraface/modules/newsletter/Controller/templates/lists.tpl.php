<h1><?php e(__('Lists')); ?></h1>

<ul class="options">
	<li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(__('Create')); ?></a></li>
</ul>

<table class="stripe">
	<caption><?php e(__('Email lists')); ?></caption>
	<thead>
	<tr>
		<th><?php e(__('Name')); ?></th>
		<th><?php e(__('Number of subscribers')); ?></th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($context->getLists() as $list): ?>
	<tr>
		<td><a href="<?php e(url($list['id'])); ?>"><?php e($list['title']); ?></a></td>
		<td><?php e($list['subscribers']); ?></td>
		<td class="options">
			<a class="edit" href="<?php e(url($list['id'], array('edit'))); ?>"><?php e(__('Edit')); ?></a>
			<a class="delete" href="<?php e(url($list['id'], array('delete'))); ?>"><?php e(__('Delete')); ?></a>
		</td>

	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
