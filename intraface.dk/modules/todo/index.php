<?php
require('../../include_first.php');

$module = $kernel->module('todo');

$todo = new TodoList($kernel);

$todo_list = $todo->getList();
$todo_done = $todo->getList('done');

$page = new Page($kernel);
$page->start('todo');
?>
<h1>Todo</h1>

<ul class="options">
	<li><a href="todo_edit.php">Opret liste</a></li>
</ul>

<?php if (count($todo_list) == 0): ?>

	<p>Der er ikke nogen todo-lister. <a href="todo_edit.php">Opret en todoliste</a>.</p>

<?php else: ?>

<ul class="todo-list">
	<?php foreach($todo_list AS $t): ?>
	<li><a href="todo.php?id=<?php echo $t['id']; ?>"><?php echo $t['name']; ?></a> &mdash; <?php echo $t['left']; ?> tilbage</li>
	<?php endforeach; ?>
</ul>

<?php endif; ?>

<?php if (count($todo_done) > 0): ?>

<p class="todo-finished"><strong>Færdige lister</strong>:
	<?php foreach($todo_done AS $t): ?>
		<a href="todo.php?id=<?php echo $t['id']; ?>"><?php echo $t['name']; ?></a>
	<?php endforeach; ?>
</p>

<?php endif; ?>

<?php
$page->end();
?>
