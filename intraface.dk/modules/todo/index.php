<?php
require('../../include_first.php');

$module = $kernel->module('todo');

$todo = new TodoList($kernel);

$todo_list = $todo->getList();
$todo_done = $todo->getList('done');

$page = new Page($kernel);
$page->start(t('Todo'));
?>
<h1><?php e(t('Todo')); ?></h1>

<ul class="options">
    <li><a href="todo_edit.php"><?php e(t('Create list')); ?></a></li>
</ul>

<?php if (count($todo_list) == 0): ?>

    <p><?php e(t('No lists available'))?>. <a href="todo_edit.php"><?php e(t('Create list')); ?></a>.</p>

<?php else: ?>

<ul class="todo-list">
    <?php foreach($todo_list AS $t): ?>
    <li><a href="todo.php?id=<?php e($t['id']); ?>"><?php e($t['name']); ?></a> &mdash; <?php e($t['left']); ?> <?php e(t('left')); ?></li>
    <?php endforeach; ?>
</ul>

<?php endif; ?>

<?php if (count($todo_done) > 0): ?>

<p class="todo-finished"><strong><?php e(t('Finished lists')); ?></strong>:
    <?php foreach($todo_done AS $t): ?>
        <a href="todo.php?id=<?php e($t['id']); ?>"><?php e($t['name']); ?></a>
    <?php endforeach; ?>
</p>

<?php endif; ?>

<?php
$page->end();
?>
