<?php
require('../../include_first.php');

$kernel->module('newsletter');

if (isset($_POST['submit'])) {

	$list = new NewsletterList($kernel, $_POST['list_id']);
	$letter = new Newsletter($list, $_POST['id']);

	if ($id = $letter->save($_POST)) {
		header('Location: letter.php?id='.$id);
		exit;
	} else {
		$value = $_POST;
	}
} elseif(isset($_GET['id'])) {

	$letter = Newsletter::factory($kernel, intval($_GET['id']));
	$value = $letter->get();
} else {
	$list = new NewsletterList($kernel, (int)$_GET['list_id']);
	if($list->get('id') == 0) {
		trigger_error('Ugyldig liste', E_USER_ERROR);
	}
	$letter = new Newsletter($list);
 	$value = array();
}

$page = new Intraface_Page($kernel);
$page->start('Rediger nyhedsbrev');

?>

<h1>Rediger nyhedsbrev</h1>

<div class="message">
	<p>Send dine nyhedsbreve:</p>
	<ul>
		<li>Mellem kl. 8.00 og 15.00</li>
		<li>Når folk har fået løn</li>
		<li><strong>aldrig</strong> i ferier eller weekender</li>
	</ul>
</div>

<?php echo $letter->error->view(); ?>

<form action="<?php e(basename($_SERVER['PHP_SELF'])); ?>" method="post">
	<fieldset>
	<input type="hidden" name="id" value="<?php e($letter->get('id')); ?>" />
	<input type="hidden" name="list_id" value="<?php e($letter->list->get('id')); ?>" />

	<div class="formrow">
		<label for="title">Titel</label>
		<input type="text" name="subject" size="60" value="<?php if (!empty($value['subject'])) e($value['subject']); ?>" />
	</div>
	<div class="formrow">
		<label for="">Tekst</label>
		<textarea name="text" cols="90" rows="20"><?php if (!empty($value['text'])) e($value['text']); ?></textarea>
	</div>
	<div class="formrow">
		<label for="title">Deadline</label>
		<input type="text" name="deadline" size="60" value="<?php if (!empty($value['deadline'])) e($value['deadline']); else e(date('Y-m-d H:i:s')); ?>" />
	</div>


	<div>
		<input type="submit" name="submit" value="Gem" class="save" />
		eller
		<a href="letters.php?list_id=<?php e($letter->list->get('id')); ?>&from_id=<?php e($letter->get('id')); ?>">Fortryd</a>
	</div>
	</fieldset>
</form>


<?php
$page->end();
?>