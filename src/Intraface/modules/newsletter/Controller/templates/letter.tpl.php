<?php
$letter = $context->getValues();
?>

<h1>Nyhedsbrev</h1>

<ul class="options">
	<?php if ($letter['status'] != 'sent'): ?>
	<li><a href="<?php e(url(null, array('edit'))); ?>">Ret</a></li>
	<?php endif; ?>
	<li><a href="<?php e(url('../')); ?>">Luk</a></li>
</ul>

<div class="box">
	<pre><h2>Overskift: <?php e($letter['subject']); ?></h2></pre>

	<pre><?php e(wordwrap($letter['text'], 80)); ?></pre>

</div>

<!-- angivelse af hvor mange det bliver sendt til -->
