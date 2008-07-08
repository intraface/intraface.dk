<?php
require('../../include_first.php');

$module = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');

$user = new UserMaintenance();


$redirect = Intraface_Redirect::factory($kernel, 'receive');

if(isset($_GET['add_user_id']) && $_GET['add_user_id'] != 0) {
	$redirect->setParameter('user_id', intval($_GET['add_user_id']));
	header('Location: '.$redirect->getRedirect('index.php'));
}

if($redirect->get('identifier') == 'add_user') {
	$add_user = true;
} else {
	$add_user = false;
}

if(isset($_GET["search"])) {

	if(isset($_GET["text"]) && $_GET["text"] != "") {
		$user->getDBQuery($kernel)->setFilter("text", $_GET["text"]);
	}
} elseif(isset($_GET['character'])) {
	$user->getDBQuery($kernel)->useCharacter();
}

$user->getDBQuery($kernel)->defineCharacter('character', 'name');
$user->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$user->getDBQuery()->storeResult("use_stored", "intranetmainenance_user", "sublevel");

$users = $user->getList();

$page = new Intraface_Page($kernel);
$page->start(t('Users'));
?>

<h1><?php print($translation->get('users')); ?></h1>


<ul class="options">
	<li><a href="index.php">Til oversigt over intranet</a></li>
	<?php
	if(isset($_GET["intranet_id"]) && intval($_GET["intranet_id"]) != 0) {
		?>
		<li><a href="user_edit.php?intranet_id=<?php print($intranet->get("id")); ?>">Opret bruger</a></li>
		<li><a href="users.php?intranet_id=<?php print($intranet->get("id")); ?>&amp;not_in_intranet=1">Tilføj eksisterende bruger</a></li>
		<?php
	}
	?>
</ul>

<form method="get" action="users.php">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('search'), 'common'); ?></legend>
		<label><?php echo safeToHtml($translation->get('search text'), 'common'); ?>:
			<input type="text" name="text" value="<?php echo $user->getDBQuery($kernel)->getFilter("text"); ?>" />
		</label>
		<span><input type="submit" name="search" value="<?php echo safeToHtml($translation->get('search', 'common')); ?>" /></span>
	</fieldset>
</form>

<?php echo $user->getDBQuery($kernel)->display('character'); ?>

<table>
<thead>
	<tr>
		<?php if($add_user): ?>
		<th></th>
		<?php endif; ?>
		<th>Navn</th>
		<th>E-mail</th>
		<th></th>
	</tr>
</thead>
<tbody>
	<?php
	for($i = 0; $i < count($users); $i++) {
		?>
		<tr>
			<?php if($add_user): ?>
			<td><a href="users.php?add_user_id=<?php print($users[$i]["id"]); ?>"><?php echo $translation->get('add', 'common'); ?></a></td>
			<?php endif; ?>
			<?php
			if($users[$i]["name"] == '') {
				$users[$i]["name"] = '['.$translation->get('not filled in', 'common').']';
			}
			?>
			<td><a href="user.php?id=<?php print($users[$i]["id"]); ?>"><?php print($users[$i]["name"]); ?></a></td>
			<td><?php print($users[$i]["email"]); ?></td>
			<td class="buttons">
				<a href="user_edit.php?id=<?php print($users[$i]["id"]); ?>" class="edit">Ret</a>
				<?php /*
				<?php if (isset($)$intranet->get('id') > 0) { ?>
				<a href="user_permission.php?id=<?php print($users[$i]["id"]); ?>&amp;intranet_id=<?php echo $intranet->get('id');?>"><?php echo $translation->get('permissions'); ?></a>
				<?php } ?>
				*/ ?>
			</td>
		</tr>
		<?php
	}
	?>
</tbody>
</table>

<?php echo $user->getDBQuery($kernel)->display('paging'); ?>

<?php
$page->end();
?>
