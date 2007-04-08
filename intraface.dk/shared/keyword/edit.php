<?php
/**
 * keywords.php
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../include_first.php');

$kernel->useShared('keyword');
$translation = $kernel->getTranslation('keyword');

if (!empty($_POST)){
	$keyword = Keyword::factory($kernel, $_POST['id']);
	if ($keyword->save($_POST)) {
		$redirect = new Redirect($kernel);
		//echo $redirect->getRedirect('/main/');
		header('Location:'.$redirect->getRedirect('/main/index.php'));
		exit;
		// hvor skal jeg sendes hen? Mon ikke vi bliver nødt til at bruge redirect?
	}
}
elseif (!empty($_GET['id'])) {
	$keyword = Keyword::factory($kernel, $_GET['id']);
}
else {
	trigger_error($translation->get('no object has been given'),E_USER_ERROR);
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit keyword')));

?>
<h1><?php echo safeToHtml($translation->get('edit keyword')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('edit keyword')); ?></legend>
		<input type="hidden" name="id" value="<?php echo $keyword->get('id'); ?>" />
		<label for="keyword"><?php echo safeToHtml($translation->get('keyword')); ?></label>
		<input type="text" name="keyword" id="keyword" value="<?php echo safeToHtmL($keyword->get('keyword')); ?>" />
		<input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" name="submit" class="save" />
	</fieldset>
</form>



<?php
$page->end();
?>
