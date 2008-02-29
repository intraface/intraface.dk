<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('controlpanel');

$user = $kernel->user;
$value = $user->get();
$address_value = $user->getAddress()->get();

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('user settings')));
?>

<h1><?php echo safeToHtml($translation->get('user settings')); ?></h1>

<ul class="options">
	<li><a href="user_edit.php"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></li>
	<li><a href="user_preferences.php"><?php echo safeToHtml($translation->get('preferences', 'controlpanel')); ?></a></li>
	<li><a href="user_change_password.php"><?php echo $translation->get('change password'); ?></a></li>
</ul>

<table class="vcard">
    <caption><?php echo safeToHtml($translation->get('information about user')); ?></caption>
    <tbody>
    <tr>
        <th><?php echo safeToHtml($translation->get('e-mail for login')); ?></th>
        <td><span class="email"><?php if (!empty($value["email"])) echo safeToHtml($value["email"]); ?></span></td>
    </tr>
</table>

<table class="vcard">
	<caption><?php echo safeToHtml($translation->get('user contact information for intranet').' '.$kernel->intranet->get('name')); ?></caption>
	<tbody>
	<tr>
		<th><?php echo safeToHtml($translation->get('name', 'address')); ?></th>
		<td class="fn"><?php if (!empty($address_value["name"])) echo safeToHtml($address_value["name"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></th>
		<td><span class="email"><?php if (!empty($address_value["email"])) echo safeToHtml($address_value["email"]); ?></span></td>
	</tr>

	<tr>
		<th><?php echo safeToHtml($translation->get('address', 'address')); ?></th>
		<td class="street-address"><?php if (!empty($address_value["address"])) echo safeToHtml($address_value["address"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('postal code and city', 'address')); ?></th>
		<td><span class="postal-code"><?php if (!empty($address_value["postcode"])) echo safeToHtml($address_value["postcode"]); ?></span> <span class="locality"> <?php if (!empty($address_value["city"])) echo safeToHtml($address_value["city"]); ?></span></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('country', 'address')); ?></th>
		<td class="country"><?php if (!empty($address_value["country"])) echo safeToHtml($address_value["country"]); ?></td>
	</tr>

	<tr>
		<th><?php echo safeToHtml($translation->get('phone', 'address')); ?></th>
		<td class="tel"><?php if (!empty($address_value["phone"])) echo safeToHtml($address_value["phone"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('website', 'address')); ?></th>
		<td class="url"><?php if (!empty($address_value["website"])) echo safeToHtml($address_value["website"]); ?></td>
	</tr>
	</tbody>
</table>

<?php
$page->end();
?>
