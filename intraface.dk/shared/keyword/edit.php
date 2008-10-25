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
		$redirect = new Intraface_Redirect($kernel);
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

$page = new Intraface_Page($kernel);
$page->start($translation->get('edit keyword'));

?>
<h1><?php e($translation->get('edit keyword')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<fieldset>
		<legend><?php e($translation->get('edit keyword')); ?></legend>
		<input type="hidden" name="id" value="<?php e($keyword->getId()); ?>" />
		<label for="keyword"><?php e($translation->getKeyword()); ?></label>
		<input type="text" name="keyword" id="keyword" value="<?php e($keyword->getKeyword()); ?>" />
		<input type="submit" value="<?php e($translation->get('save', 'common')); ?>" name="submit" class="save" />
	</fieldset>
</form>



<?php
$page->end();
?>
