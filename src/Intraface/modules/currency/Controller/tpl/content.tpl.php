<h1><?php e(t($context->document()->title())); ?></h1>

<?php if ($context->document()->options()): ?>
<ul class="options">
    <?php foreach ($context->document()->options() as $url => $option): ?>
    <li><a href="<?php e($url); ?>"><?php e($option); ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php echo $content; ?>
