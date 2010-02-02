<h1><?php e(t('cms')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('create')); ?>"><?php e(t('create site')); ?></a></li>
</ul>

<?php if (is_array($sites) AND count($sites) == 0): ?>
	<p><?php e(t('no sites created')); ?></p>
<?php else: ?>
<table>
<caption><?php e(t('sites')); ?></caption>
<thead>
	<tr>
		<th><?php e(t('name')); ?></th>
		<th><?php e(t('go directly to')); ?></th>
        <th></th>
	</tr>
</thead>
<tbody>
<?php foreach ($sites AS $s): ?>
<tr>
	<td><a href="<?php e(url($s['id'])); ?>"><?php e($s['name']);  ?></a></td>
	<td>
		<a href="<?php e(url($s['id'] . '/pages', array('type' => 'page'))); ?>"><?php e(t('pages'));  ?></a>,
		<a href="<?php e(url($s['id'] . '/pages', array('type' => 'article'))); ?>"><?php e(t('articles'));  ?></a>,
		<a href="<?php e(url($s['id'] . '/pages', array('type' => 'news'))); ?>"><?php e(t('news'));  ?></a>
	</td>
    <td class="options">
		<a class="edit" href="<?php e(url($s['id'] . '/edit')); ?>"><?php e(t('edit settings')); ?></a>
		<a class="delete" href="<?php e(url(null, array('delete' => $s['id']))); ?>"><?php e(t('Delete')); ?></a>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>