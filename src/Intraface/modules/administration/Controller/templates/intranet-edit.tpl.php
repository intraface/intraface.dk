<h1><?php e(t('edit intranet')); ?></h1>

<?php echo $intranet->error->view(); ?>
<?php echo $intranet->address->error->view(); ?>

<?php if (isset($filehandler)) echo $filehandler->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend><?php e(t('information about the intranet')); ?></legend>
		<div class="formrow">
			<label for="name"><?php e(t('name')); ?></label>
			<input type="text" name="name" id="name" value="<?php if (isset($values['name'])) e($values["name"]); ?>" size="50" />

  		</div>
  		<div class="formrow">
			<label for="identifier"><?php e(t('identifier', 'common')); ?></label>
			<input type="text" name="identifier" id="identifier" value="<?php if (isset($values['identifier'])) e($values["identifier"]); ?>" size="50" />

		</div>

	</fieldset>



	<fieldset>
		<legend><?php e(t('address information')); ?></legend>
		<div class="formrow">
			<label for="address_name"><?php e(t('name', 'address')); ?></label>
			<input type="text" name="address_name" id="address_name" value="<?php if (isset($address['name'])) e($address["name"]); ?>" />
		</div>
		<div class="formrow">
			<label for="address"><?php e(t('address', 'address')); ?></label>
			<textarea name="address" id="address" rows="2"><?php if (isset($address['address'])) e($address["address"]); ?></textarea>
		</div>
		<div class="formrow">
			<label for="postcode"><?php e(t('postal code and city', 'address')); ?></label>
			<div>
				<input type="text" name="postcode" id="postcode" value="<?php if (isset($address['postcode'])) e($address["postcode"]); ?>" size="4" />
				<input type="text" name="city" id="city" value="<?php if (isset($address['city'])) e($address["city"]); ?>" />
			</div>
		</div>
		<div class="formrow">
			<label for="country"><?php e(t('country', 'address')); ?></label>
			<input type="text" name="country" id="country" value="<?php if (isset($address['country'])) e($address["country"]); ?>" />
		</div>
		<div class="formrow">
			<label for="cvr"><?php e(t('cvr number', 'address')); ?></label>
			<input type="text" name="cvr" id="cvr" value="<?php if (isset($address['cvr'])) e($address["cvr"]); ?>" />
		</div>
		<div class="formrow">
			<label for="email"><?php e(t('e-mail', 'address')); ?></label>
			<input type="text" name="email" id="email" value="<?php if (isset($address['email'])) e($address["email"]); ?>" />
		</div>
		<div class="formrow">
			<label for="website"><?php e(t('website', 'address')); ?></label>
			<input type="text" name="website" id="website" value="<?php if (isset($address['website'])) e($address["website"]); ?>" />
		</div>
		<div class="formrow">
			<label for="phone"><?php e(t('phone', 'address')); ?></label>
			<input type="text" name="phone" id="phone" value="<?php if (isset($address['phone'])) e($address["phone"]); ?>" />
		</div>
	</fieldset>

	<fieldset>
		<legend><?php e(t('header for pdf')); ?></legend>
		<?php
		$filehandler = new FileHandler($kernel, $intranet->get('pdf_header_file_id'));
		$filehandler_html = new FileHandlerHTML($filehandler);
		$filehandler_html->printFormUploadTag('pdf_header_file_id','new_pdf_header_file', 'choose_file', array('image_size' => 'small'));
		?>
		<p><?php e(t('Header should be a .jpg image. For best results make the picture 150px tall')); ?></p>
	</fieldset>

	<div style="clear:both;">
		<input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" /> <a href="/main/controlpanel/intranet.php"><?php e(t('Cancel', 'common')); ?></a>
	</div>
</form>
