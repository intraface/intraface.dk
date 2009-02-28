<h1>Intraface Developer Tools</h1>

<ul>
    <?php foreach ($this->document->navigation as $url => $name): ?>
    <li><a href="<?php e($url); ?>"><?php e($name); ?></a>
    <?php endforeach; ?>
</ul>