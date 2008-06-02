<?php
require '../../include_first.php';

$kernel->useShared('filehandler');

$translation = $kernel->getTranslation('controlpanel');


$values = $kernel->intranet->get();
$address = $kernel->intranet->address->get();

$page = new Intraface_Page($kernel);

$page->start(t('about the intranet'));
?>

<h1><?php e(t('about the intranet')); ?></h1>

<ul class="options">
	<li><a href="index.php"><?php e(t('close', 'common')); ?></a></li>
<?php if ($kernel->user->hasModuleAccess('administration')): $administration_module = $kernel->useModule('administration'); ?>
	<li><a href="<?php echo $administration_module->getPath(); ?>intranet_edit.php"><?php e(t('edit', 'common')); ?></a></li>
<?php endif; ?>
</ul>

<?php echo $kernel->intranet->error->view(); ?>

<table class="vcard">
	<caption><?php e(t('information about the intranet')); ?></caption>
	<tr>
		<th><?php e(t('intranet name')); ?></th>
		<td><?php e($values["name"]); ?></td>
  </tr>

 	<tr>
		<th><?php e(t('identifier', 'common')); ?></th>
		<td><?php e($values["identifier"]); ?></td>
  </tr>
	<?php if ($kernel->user->hasModuleAccess('administration')): ?>
	<tr>
		<td colspan="2"><b><?php e(t('intranet keys - used when accessing the system from outside')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('private key')); ?></th>
		<td><?php e($kernel->intranet->get("private_key")); ?></td>
	</tr>
	<tr>
		<th><?php e(t('public key')); ?></th>
		<td><?php e($kernel->intranet->get("public_key")); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td colspan="2"><b><?php e(t('intranet address')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('name', 'address')); ?></th>
		<td class="fn"><?php if(isset($address['name'])) e($address["name"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('address', 'address')); ?></th>
		<td class="street-address"><?php if(isset($address['address'])) e($address["address"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('postal code and city', 'address')); ?></th>
		<td>
			<span class="postal-code"><?php if(isset($address['postcode'])) e($address["postcode"]); ?></span>
			<span class="locality"><?php if(isset($address['city'])) e($address["city"]); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php e(t('country', 'address')); ?></th>
		<td class="country"><?php if(isset($address['country'])) e($address["country"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('cvr number', 'address')); ?></th>
		<td><?php if(isset($address['cvr'])) e($address["cvr"]); ?>
			<?php if (!empty($address['cvr']) AND strlen($address['cvr']) == 8): ?>
				(<a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php echo $address['cvr']; ?>">se opslaget på virk.dk</a>)
			<?php endif;?>
		</td>
	</tr>
	<tr>
		<th><?php e(t('e-mail', 'address')); ?></th>
		<td class="email"><?php if(isset($address['email'])) e($address["email"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('website', 'address')); ?></th>
		<td class="url"><?php if(isset($address['website'])) e($address["website"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('phone', 'address')); ?></th>
		<td class="tel"><?php if(isset($address['phone'])) e($address["phone"]); ?></td>
	</tr>
</table>


<div class="box">
	<h2><?php e(t('header to pdf-documents')); ?></h2>

	<?php
	$filehandler = new FileHandler($kernel, $kernel->intranet->get('pdf_header_file_id'));
	if($filehandler->get('id') > 0) {

		$filehandler->createInstance('medium');
		echo '<img src="'.$filehandler->instance->get('file_uri').'" alt="Sidehoved til breve" style="width: '.$filehandler->instance->get('width').'px; height: '.$filehandler->instance->get('height').'px;" />';
	} else {

		echo '<p>' . e($translation->get('no picture uploaded'));
		if($kernel->user->hasModuleAccess('administration')) {
			$module_administration = $kernel->useModule('administration');
			echo ' <a href="'.$module_administration->getPath().'intranet_edit.php">'.e($translation->get('upload picture')).'</a>.';
		}
		echo '</p>';
	}
	?>
</div>

<?php
$page->end();
?>