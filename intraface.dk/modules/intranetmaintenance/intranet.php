<?php
require('../../include_first.php');

$modul = $kernel->module("intranetmaintenance");
if($kernel->user->hasModuleAccess('contact')) {
	$contact_module = $kernel->useModule('contact');
}
$translation = $kernel->getTranslation('intranetmaintenance');

# add contact
if(isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
	$intranet = new IntranetMaintenance($kernel, $_GET['id']);
	if($kernel->user->hasModuleAccess('contact')) {
		$contact_module = $kernel->useModule('contact');

		$redirect = Redirect::factory($kernel, 'go');
		$url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $modul->getPath()."intranet.php?id=".$intranet->get('id'));
		$redirect->askParameter('contact_id');
		$redirect->setIdentifier('contact');

		header("location: ".$url);
		exit;
	}
	else {
		trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
	}
}

# add existing user
if(isset($_GET['add_user']) && $_GET['add_user'] == 1) {
	$intranet = new IntranetMaintenance($kernel, $_GET['id']);


	$redirect = Redirect::factory($kernel, 'go');
	$url = $redirect->setDestination($modul->getPath()."users.php", $modul->getPath()."user.php?intranet_id=".$intranet->get('id'));
	$redirect->askParameter('user_id');
	$redirect->setIdentifier('add_user');
	header("location: ".$url);
	exit;
}

#return
if(isset($_GET['return_redirect_id'])) {
	$intranet = new IntranetMaintenance($kernel, $_GET['id']);
	$redirect = Redirect::factory($kernel, 'return');
	if($redirect->get('identifier') == 'contact') {
		$intranet->setContact($redirect->getParameter('contact_id'));
	}
}

# Update permission
if(isset($_POST["submit"])) {

	$intranet = new IntranetMaintenance($kernel, intval($_POST["id"]));

	$modules = array();
	$modules = $_POST["module"];

	$intranet->flushAccess();

	// Hvis man er i det samme intranet som man redigere
	if($kernel->intranet->get("id") == $intranet->get("id")) {
		// Finder det aktive modul
		$active_module = $kernel->getPrimaryModule();
		// Giver adgang til det
		$intranet->setModuleAccess($active_module->getId());
	}

	for($i = 0, $max = count($modules); $i < $max; $i++) {
		$intranet->setModuleAccess($modules[$i]);
	}

	header('Location: intranet.php?id='.$intranet->get('id'));
	exit;
}
else {
	$intranet = new IntranetMaintenance($kernel, (int)$_GET["id"]);
}



$value = $intranet->get();
if(isset($intranet->address)) {
	$address_value = $intranet->address->get();
}
else {
	$address_value = array();
}



$user = new UserMaintenance($kernel);
$user->setIntranetId($intranet->get('id'));

$page = new Page($kernel);
$page->start($translation->get('Intranet'));
?>

<div id="colOne">

<h1><?php print $translation->get('Intranet'); ?>: <?php echo safeToHtml($intranet->get('name')); ?></h1>

<ul class="options">
	<li><a href="intranet_edit.php?id=<?php echo $intranet->get('id'); ?>"><?php echo $translation->get('edit', 'common'); ?></a></li>
	<li><a href="index.php?use_stored=true"><?php echo $translation->get('close', 'common'); ?></a></li>
</ul>

<?php echo $intranet->error->view(); ?>

<table>
	<tr>
		<th><?php echo $translation->get('name', 'address'); ?></th>
		<td>
			<?php if(isset($value['name'])) print safeToHtml($value["name"]); ?>
			<?php if (!empty($value['contact_id']) AND $intranet->get('id') > 0 && isset($contact_module)): ?>
				<?php
					$contact = new Contact($kernel, $value['contact_id']);
					echo '<a href="'.$contact_module->getPath() .'contact.php?id='.$contact->get('id').'">'.$contact->get('name').'</a>';
					echo ' <a href="'.basename($_SERVER['PHP_SELF']).'?id='.$intranet->get('id').'&amp;add_contact=1">'.$translation->get('change contact').'</a>';
				?>
			<?php elseif(isset($contact_module)): ?>
				<a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?id=<?php echo $intranet->get('id'); ?>&amp;add_contact=1"><?php echo $translation->get('add contact'); ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<!--
	<tr>
		<th><?php echo $translation->get('maintained by'); ?></th>
		<td></td>
	</tr>
	-->


	<tr>
		<th><?php echo $translation->get('name', 'address'); ?></th>
		<td><?php if(isset($address_value["name"])) print safeToHtml($address_value["name"]); ?></td>
	</tr>

	<tr>
		<th><?php echo $translation->get('address', 'address'); ?></th>
		<td><?php if(isset($address_value["address"])) print safeToHtml($address_value["address"]); ?></td>
	</tr>

	<tr>
		<th><?php echo $translation->get('postal code and city', 'address'); ?></th>
		<td><?php if(isset($address_value["postcode"])) print safeToHtml($address_value["postcode"]); ?> <?php if(isset($address_value["city"])) print safeToHtml($address_value["city"]); ?></td>
	</tr>
	<tr>
		<th><?php echo $translation->get('country', 'address'); ?></th>
		<td><?php if(isset($address_value["country"])) print safeToHtml($address_value["country"]); ?></td>
	</tr>
	<tr>
		<th><?php echo $translation->get('cvr number', 'address'); ?></th>
		<td><?php if(isset($address_value["cvr"])) print safeToHtml($address_value["cvr"]); ?></td>
	</tr>
	<tr>
		<th><?php echo $translation->get('e-mail', 'address'); ?></th>
		<td><?php if(isset($address_value["email"])) print safeToHtml($address_value["email"]); ?></td>
	</tr>

	<tr>
		<th><?php echo $translation->get('website', 'address'); ?></th>
		<td><?php if(isset($address_value["website"])) print safeToHtml($address_value["website"]); ?></td>
	</tr>

	<tr>
		<th><?php echo $translation->get('phone', 'address'); ?></th>
		<td><?php if(isset($address_value["phone"])) print safeToHtml($address_value["phone"]); ?></td>
	</tr>

		<tr>
		<th><?php echo $translation->get('private key'); ?></th>
		<td><?php print safeToHtml($intranet->get("private_key")); ?></td>
	</tr>

	<tr>
		<th><?php echo $translation->get('public key'); ?></th>
		<td><?php print safeToHtml($intranet->get("public_key")); ?></td>
	</tr>

</table>

<form action="intranet.php" method="post">


<fieldset>
	<legend>Adgang til moduler</legend>

	<?php

	$module = new ModuleMaintenance($kernel);
	$modules = $module->getList();

	for($i = 0; $i < count($modules); $i++) {
		?>
		<div>
			<input type="checkbox" name="module[]" id="module<?php print($modules[$i]["id"]); ?>" value="<?php print($modules[$i]["id"]); ?>"<?php if($intranet->hasModuleAccess(intval($modules[$i]["id"]))) print("checked=\"checked\""); ?> />
			<label for="module<?php print($modules[$i]["id"]); ?>"><?php print($modules[$i]["menu_label"]); ?></label>
		</div>
		<?php
	}
	?>
</fieldset>

<input type="hidden" name="id" value="<?php print($intranet->get("id")); ?>" />
<input type="submit" name="submit" value="Gem" />

</form>


</div>

<div id="colTwo">

<table class="stribe">
	<caption>Users</caption>
	<thead>
	<tr>
		<th>Navn</th>
		<th>E-mail</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$users = $user->getList();

	foreach($users AS $user_list) {
		?>
		<tr>
			<?php
			if($user_list['name'] == '') $user_list['name'] = '[not filled in]';
			?>
			<td><a href="user.php?id=<?php print(intval($user_list['id'])); ?>&amp;intranet_id=<?php print($intranet->get('id')); ?>"><?php print(safeToHtml($user_list['name'])); ?></a></td>
			<td><?php print(safeToHtml($user_list['email'])); ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>

<p><a href="user_edit.php?intranet_id=<?php echo intval($intranet->get('id')); ?>">Create new user</a></p>

<p><a href="intranet.php?id=<?php echo intval($intranet->get('id')); ?>&amp;add_user=1">Add existing user</a></p>



</div>

<?php
$page->end();
?>
