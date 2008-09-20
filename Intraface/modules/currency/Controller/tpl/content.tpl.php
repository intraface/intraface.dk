<h1><?php e(t($this->document->title)); ?></h1>

<?php if (!empty($this->document->options)): ?>
<ul class="options">
    <?php foreach ($this->document->options as $url => $option): ?>
    <li><a href="<?php e($url); ?>"><?php e($option); ?></a></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php echo $content; ?>
