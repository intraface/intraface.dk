<?php
/**
 * Kig lige på om vi skal gøre noget ved http_referer - den er jo i hvert fald ikke så sikker.
 *
 */


require('../include_first.php');

$translation = $kernel->getTranslation('common');

if (isset($_GET["id"]) && $kernel->user->hasIntranetAccess($_GET['id'])) {
	if ($kernel->user->setActiveIntranetId(intval($_GET['id']))) {
		header('Location: index.php');
		exit;
	}
	else {
		trigger_error($translation->get('could not change intranet'), E_USER_ERROR);
	}
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('change intranet')));

?>
<h1><?php echo safeToHtml($translation->get('change intranet')); ?></h1>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="get">
	<fieldset id="choose_intranet" class="radiobuttons">
	<legend><?php echo safeToHtml($translation->get('choose intranet')); ?></legend>
	<?php
	// bør hente en liste vha. intranethalløjsaen
	$db = new Db_sql;
	$db->query("SELECT * FROM intranet ORDER BY name");
	while($db->nextRecord()) {
		if(!$kernel->user->hasIntranetAccess($db->f("id"))) { continue; }
		?>
		<label<?php if ($kernel->intranet->get('id') == $db->f('id')) echo ' class="selected"' ?> for="intranet_<?php echo $db->f("id"); ?>"><input type="radio" name="id" value="<?php print($db->f("id")); ?>" <?php if ($kernel->intranet->get('id') == $db->f('id')) echo ' checked="checked"'; ?> id="intranet_<?php echo $db->f('id'); ?>" /> <?php echo safeToHtml($db->f("name")); ?></label>
		<?php
	}
	?>
	</fieldset>
	<div>
		<input type="submit" value="<?php echo safeToHtml($translation->get('change')); ?>" /> <a href="<?php if(isset($_SERVER['HTTP_REFERER'])): echo safeToHtml($_SERVER['HTTP_REFERER']); else: echo 'index.php'; endif; ?>"><?php echo safeToHtml($translation->get('regret')); ?></a>
	</div>

</form>

<?php
$page->end();
?>
