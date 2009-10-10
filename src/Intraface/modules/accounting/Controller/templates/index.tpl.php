<h1>Regnskab</h1>

<div class="message">
    <p><strong>Regnskab</strong>. I dette modul kan du lave dit virksomhedsregnskab.</p>
</div>

<?php if (count($context->getYear()->getList()) == 0): ?>
    <p>Du skal <a href="<?php e($context->url('year', array('create'))); ?>">oprette et regnskab</a> for at komme i gang med at bruge regnskabsmodulet.</p>
<?php else: ?>
    <p><a href="<?php e($context->url('year')); ?>">Vælg et regnskab</a> du vil se eller ændre i.</p>
<?php endif; ?>
