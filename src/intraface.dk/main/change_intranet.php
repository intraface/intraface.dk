<?php
require '../include_first.php';

$translation = $kernel->getTranslation('common');

if (isset($_GET["id"]) && $kernel->user->hasIntranetAccess($_GET['id'])) {
    // @todo make sure a new user is stored in Auth, otherwise
    //       the access to the modules are not correctly maintained.
    //       Right now I just clear permisions when getting the new user
    //       which probably is the most clever solution.
	if ($kernel->user->setActiveIntranetId(intval($_GET['id']))) {
		header('Location: index.php');
		exit;
	} else {
		trigger_error(__('could not change intranet'), E_USER_ERROR);
	}
}

// @todo bør hente en liste vha. intranethalløjsaen
$db = new DB_Sql;
$db->query("SELECT * FROM intranet ORDER BY name");
$accessible_intranets = array();
while ($db->nextRecord()) {
    if (!$kernel->user->hasIntranetAccess($db->f("id"))) {
        continue;
    }
    $accessible_intranets[$db->f('id')] = $db->f('name');
}

$page = new Intraface_Page($kernel);
$page->start(t('change intranet'));
?>
<h1><?php e(t('change intranet')); ?></h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="get">
	<fieldset id="choose_intranet" class="radiobuttons">
	<legend><?php e(t('choose intranet')); ?></legend>
	<?php foreach ($accessible_intranets as $id => $name): ?>
		<label<?php if ($kernel->intranet->get('id') == $id) echo ' class="selected"' ?> for="intranet_<?php e($id); ?>"><input type="radio" name="id" value="<?php e($id); ?>" <?php if ($kernel->intranet->get('id') == $id) echo ' checked="checked"'; ?> id="intranet_<?php e($id); ?>" /> <?php e($name); ?></label>
	<?php endforeach; ?>
	</fieldset>
	<div>
		<input type="submit" value="<?php e(t('change')); ?>" /> <a href="<?php if (isset($_SERVER['HTTP_REFERER'])): e($_SERVER['HTTP_REFERER']); else: echo 'index.php'; endif; ?>"><?php e(t('Cancel')); ?></a>
	</div>

</form>

<?php
$page->end();
?>