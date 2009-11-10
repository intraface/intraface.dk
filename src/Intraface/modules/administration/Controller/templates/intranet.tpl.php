<?php
$values = $context->getValues();
$address = $context->getValues();
?>

<h1><?php e(t('about the intranet')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Close', 'common')); ?></a></li>
	<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit', 'common')); ?></a></li>
</ul>

<?php echo $context->getKernel()->intranet->error->view(); ?>

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
	<?php if ($context->getKernel()->user->hasModuleAccess('administration')): ?>
	<tr>
		<td colspan="2"><b><?php e(t('intranet keys - used when accessing the system from outside')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('private key')); ?></th>
		<td><?php e($context->getKernel()->intranet->get("private_key")); ?></td>
	</tr>
	<tr>
		<th><?php e(t('public key')); ?></th>
		<td><?php e($context->getKernel()->intranet->get("public_key")); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td colspan="2"><b><?php e(t('intranet address')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('name', 'address')); ?></th>
		<td class="fn"><?php if (isset($address['name'])) e($address["name"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('address', 'address')); ?></th>
		<td class="street-address"><?php if (isset($address['address'])) e($address["address"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('postal code and city', 'address')); ?></th>
		<td>
			<span class="postal-code"><?php if (isset($address['postcode'])) e($address["postcode"]); ?></span>
			<span class="locality"><?php if (isset($address['city'])) e($address["city"]); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php e(t('country', 'address')); ?></th>
		<td class="country"><?php if (isset($address['country'])) e($address["country"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('cvr number', 'address')); ?></th>
		<td><?php if (isset($address['cvr'])) e($address["cvr"]); ?>
			<?php if (!empty($address['cvr']) AND strlen($address['cvr']) == 8): ?>
				(<a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php e($address['cvr']); ?>">se opslaget på virk.dk</a>)
			<?php endif;?>
		</td>
	</tr>
	<tr>
		<th><?php e(t('e-mail', 'address')); ?></th>
		<td class="email"><?php if (isset($address['email'])) e($address["email"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('website', 'address')); ?></th>
		<td class="url"><?php if (isset($address['website'])) e($address["website"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('phone', 'address')); ?></th>
		<td class="tel"><?php if (isset($address['phone'])) e($address["phone"]); ?></td>
	</tr>
</table>


<div class="box">
	<h2><?php e(t('header to pdf-documents')); ?></h2>

	<?php
	$filehandler = new FileHandler($context->getKernel(), $context->getKernel()->intranet->get('pdf_header_file_id'));
	if ($filehandler->get('id') > 0) {

		$filehandler->createInstance('medium');
		echo '<img src="'.$filehandler->instance->get('file_uri').'" alt="Sidehoved til breve" style="width: '.$filehandler->instance->get('width').'px; height: '.$filehandler->instance->get('height').'px;" />';
	} else {

		echo '<p>' . e(__('no picture uploaded'));
		if ($context->getKernel()->user->hasModuleAccess('administration')) {
			$module_administration = $context->getKernel()->useModule('administration');
			echo ' <a href="'.$module_administration->getPath().'intranet_edit.php">'.e(__('upload picture')).'</a>.';
		}
		echo '</p>';
	}
	?>
</div>