<?php
require('../../include_first.php');
$kernel->module('newsletter');


if (!empty($_POST)) {

	$letter = Newsletter::factory($kernel, (int)$_POST['id']);

	if ($letter->queue()) {
		header('Location: letters.php?list_id='.$letter->list->get('id'));
		exit;
	}
}
elseif (!empty($_GET)) {
	$letter = Newsletter::factory($kernel, (int)$_GET['id']);
}
else {
	trigger_error('Der mangler et nyhedsbrev', FATAL);
}

$page = new Page($kernel);
$page->start('Send nyhedsbrev');
?>

<h1>Send nyhedsbrev</h1>

<ul class="options">
	<li><a href="letter_edit.php?id=<?php echo $letter->get('id'); ?>">Ret</a></li>
</ul>

<?php echo $letter->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo $letter->get('id'); ?>" />

	<fieldset>
		<legend>Emne</legend>
		<p><?php echo $letter->get('subject'); ?></p>
	</fieldset>
	<fieldset>
		<legend>Tekst</legend>
		<div>
    	<pre><?php echo wordwrap($letter->get('text') . "\n\n" . $letter->list->get('unsubscribe'), 72); ?></pre>
    </div>
	</fieldset>
  <div>
		<input type="submit" name="submit" value="Send" />
		eller
		<a href="letters.php?list_id=<?php echo $letter->list->get('id'); ?>">Fortryd</a>
   </div>
</form>


<?php
$page->end();
?>