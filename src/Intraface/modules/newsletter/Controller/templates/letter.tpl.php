<?php
$letter = $context->getValues();
?>

<h1><?php e(t('Letter')); ?></h1>

<ul class="options">
	<?php if ($letter['status'] != 'sent'): ?>
	<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
	<?php endif; ?>
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if ($context->query('flare')): ?>
	<p><?php e(t($context->query('flare'))); ?></p>
<?php endif; ?>

<div class="box">
	<h2><?php e(t('Subject')); ?>: <?php e($letter['subject']); ?></h2>

	<pre><?php e(wordwrap($letter['text'], 80)); ?></pre>

</div>

<!-- @todo angivelse af hvor mange det bliver sendt til, hvornår det er sendt mv. -->
