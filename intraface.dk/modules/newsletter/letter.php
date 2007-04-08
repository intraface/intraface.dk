<?php
require('../../include_first.php');

$kernel->module('newsletter');

$newsletter = Newsletter::factory($kernel, $_GET['id']);
$letter = $newsletter->get();
$letter['list_id'] = $newsletter->list->get('id');

$page = new Page($kernel);
$page->start('Rediger nyhedsbrev');

?>

<h1>Nyhedsbrev</h1>

<ul class="options">
	<?php if ($letter['status'] != 'sent'): ?>
	<li><a href="letter_edit.php?id=<?php echo $letter['id']; ?>">Ret</a></li>
	<?php endif; ?>
	<li><a href="letters.php?list_id=<?php echo $letter['list_id']; ?>">Luk</a></li>
</ul>

<div class="box">
	<pre><h2>Overskift: <?php echo $letter['subject']; ?></h2></pre>

	<pre><?php echo wordwrap($letter['text'], 80); ?></pre>

</div>

<!-- angivelse af hvor mange det bliver sendt til -->

<?php
$page->end();
?>