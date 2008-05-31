<?php
/**
 * Table structure should probably be altered --> one table in stead of three
 *
 *
 */

require('../../include_first.php');

$kernel->useShared('filehandler');

$translation = $kernel->getTranslation('controlpanel');



$values = $kernel->intranet->get();
$address = $kernel->intranet->address->get();

$page = new Intraface_Page($kernel);

$page->start(safeToHtml($translation->get('about the intranet')));

?>

<h1><?php echo safeToHtml($translation->get('about the intranet')); ?></h1>

<ul class="options">
	<li><a href="index.php"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
<?php if ($kernel->user->hasModuleAccess('administration')): $administration_module = $kernel->useModule('administration'); ?>
	<li><a href="<?php echo $administration_module->getPath(); ?>intranet_edit.php"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></li>
<?php endif; ?>
</ul>

<?php echo $kernel->intranet->error->view(); ?>

<table class="vcard">
	<caption><?php echo safeToHtml($translation->get('information about the intranet')); ?></caption>
	<tr>
		<th><?php echo safeToHtml($translation->get('intranet name')); ?></th>
		<td><?php echo safeToHtml($values["name"]); ?></td>
  </tr>

 	<tr>
		<th><?php echo safeToHtml($translation->get('identifier', 'common')); ?></th>
		<td><?php echo safeToHtml($values["identifier"]); ?></td>
  </tr>
	<?php if ($kernel->user->hasModuleAccess('administration')): ?>
	<tr>
		<td colspan="2"><b><?php echo safeToHtml($translation->get('intranet keys - used when accessing the system from outside')); ?></b></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('private key')); ?></th>
		<td><?php print safeToHtml($kernel->intranet->get("private_key")); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('public key')); ?></th>
		<td><?php print safeToHtml($kernel->intranet->get("public_key")); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td colspan="2"><b><?php echo safeToHtml($translation->get('intranet address')); ?></b></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('name', 'address')); ?></th>
		<td class="fn"><?php if(isset($address['name'])) echo safeToHtml($address["name"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('address', 'address')); ?></th>
		<td class="street-address"><?php if(isset($address['address'])) echo safeToHtml($address["address"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('postal code and city', 'address')); ?></th>
		<td>
			<span class="postal-code"><?php if(isset($address['postcode'])) echo safeToHtml($address["postcode"]); ?></span>
			<span class="locality"><?php if(isset($address['city'])) echo safeToHtml($address["city"]); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('country', 'address')); ?></th>
		<td class="country"><?php if(isset($address['country'])) echo safeToHtml($address["country"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('cvr number', 'address')); ?></th>
		<td><?php if(isset($address['cvr'])) echo safeToHtml($address["cvr"]); ?>
			<?php if (!empty($address['cvr']) AND strlen($address['cvr']) == 8): ?>
				(<a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php echo $address['cvr']; ?>">se opslaget på virk.dk</a>)
			<?php endif;?>
		</td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></th>
		<td class="email"><?php if(isset($address['email'])) echo safeToHtml($address["email"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('website', 'address')); ?></th>
		<td class="url"><?php if(isset($address['website'])) echo safeToHtml($address["website"]); ?></td>
	</tr>
	<tr>
		<th><?php echo safeToHtml($translation->get('phone', 'address')); ?></th>
		<td class="tel"><?php if(isset($address['phone'])) echo safeToHtml($address["phone"]); ?></td>
	</tr>
</table>


<div class="box">
	<h2><?php echo safeToHtml($translation->get('header to pdf-documents')); ?></h2>

	<?php
	$filehandler = new FileHandler($kernel, $kernel->intranet->get('pdf_header_file_id'));
	if($filehandler->get('id') > 0) {

		$filehandler->createInstance('medium');
		echo '<img src="'.$filehandler->instance->get('file_uri').'" alt="Sidehoved til breve" style="width: '.$filehandler->instance->get('width').'px; height: '.$filehandler->instance->get('height').'px;" />';
	}
	else {

		echo '<p>' . safeToHtml($translation->get('no picture uploaded'));
		if($kernel->user->hasModuleAccess('administration')) {
			$module_administration = $kernel->useModule('administration');
			echo ' <a href="'.$module_administration->getPath().'intranet_edit.php">'.safeToHtml($translation->get('upload picture')).'</a>.';
		}
		echo '</p>';
	}
	?>
</div>

<?php
$page->end();
?>