<h1><?php e(t('Todo')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('create'));?>"><?php e(t('Create list')); ?></a></li>
</ul>

<?php if (count($todo_list) == 0): ?>

    <p><?php e(t('No lists available'))?>. <a href="<?php e(url('create'));?>"><?php e(t('Create list')); ?></a>.</p>

<?php else: ?>

<ul class="todo-list">
    <?php foreach ($todo_list AS $t): ?>
    <li><a href="<?php e(url($t['id'])); ?>"><?php e($t['name']); ?></a> &mdash; <?php e($t['left']); ?> <?php e(t('left')); ?></li>
    <?php endforeach; ?>
</ul>

<?php endif; ?>

<?php if (count($todo_done) > 0): ?>

<p class="todo-finished"><strong><?php e(t('Finished lists')); ?></strong>:
    <?php foreach ($todo_done AS $t): ?>
        <a href="<?php e(url($t['id'])); ?>"><?php e($t['name']); ?></a>
    <?php endforeach; ?>
</p>

<?php endif; ?>
