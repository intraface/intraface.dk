<?php
$values = $context->getValues();
$address = $context->getValues();
?>

<h1><?php e(t('About the intranet')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
	<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
</ul>

<?php echo $context->getKernel()->intranet->error->view(); ?>

<table class="vcard">
	<caption><?php e(t('Information about the intranet')); ?></caption>
	<tr>
		<th><?php e(t('Intranet name')); ?></th>
		<td><?php e($values["name"]); ?></td>
  </tr>

 	<tr>
		<th><?php e(t('Identifier')); ?></th>
		<td><?php e($values["identifier"]); ?></td>
  </tr>
	<?php if ($context->getKernel()->user->hasModuleAccess('administration')): ?>
	<tr>
		<td colspan="2"><b><?php e(t('Intranet keys - used when accessing the system from outside')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('Private key')); ?></th>
		<td><?php e($context->getKernel()->intranet->get("private_key")); ?></td>
	</tr>
	<tr>
		<th><?php e(t('Public key')); ?></th>
		<td><?php e($context->getKernel()->intranet->get("public_key")); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td colspan="2"><b><?php e(t('Intranet address')); ?></b></td>
	</tr>
	<tr>
		<th><?php e(t('Name')); ?></th>
		<td class="fn"><?php if (isset($address['name'])) e($address["name"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('Address')); ?></th>
		<td class="street-address"><?php if (isset($address['address'])) e($address["address"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('Postal code and city')); ?></th>
		<td>
			<span class="postal-code"><?php if (isset($address['postcode'])) e($address["postcode"]); ?></span>
			<span class="locality"><?php if (isset($address['city'])) e($address["city"]); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php e(t('Country')); ?></th>
		<td class="country"><?php if (isset($address['country'])) e($address["country"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('CVR-number')); ?></th>
		<td><?php if (isset($address['cvr'])) e($address["cvr"]); ?>
			<?php if (!empty($address['cvr']) AND strlen($address['cvr']) == 8): ?>
				(<a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php e($address['cvr']); ?>">se opslaget på virk.dk</a>)
			<?php endif;?>
		</td>
	</tr>
	<tr>
		<th><?php e(t('Email')); ?></th>
		<td class="email"><?php if (isset($address['email'])) e($address["email"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('Website')); ?></th>
		<td class="url"><?php if (isset($address['website'])) e($address["website"]); ?></td>
	</tr>
	<tr>
		<th><?php e(t('Phone')); ?></th>
		<td class="tel"><?php if (isset($address['phone'])) e($address["phone"]); ?></td>
	</tr>
</table>


<div class="box">
	<h2><?php e(t('Header to pdf-documents')); ?></h2>
	<?php
	$filehandler = $context->getFilehandler();
	if ($filehandler->get('id') > 0) {
		$filehandler->createInstance('medium'); ?>
        <img src="<?php  e($filehandler->instance->get('file_uri')); ?>" alt="Sidehoved til breve" style="width: <?php e($filehandler->instance->get('width')); ?>px; height: <?php e($filehandler->instance->get('height')); ?>px;" />
	<?php } else { ?>
		<p><?php e(t('No picture uploaded')); ?> <a href="'.'"><?php e(t('upload picture')); ?></a>. </p>
	<?php } ?>
</div>