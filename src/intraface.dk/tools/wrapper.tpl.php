<ul>
<?php foreach ($navigation as $url => $name): ?>
	<li><a href="<?php e($url); ?>"><?php e($name); ?></a></li>
<?php endforeach; ?>
</ul>