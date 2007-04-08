<?php
require('../../include_first.php');

$modul = $kernel->module('administration');
$shared_filehandler = $kernel->useShared('filehandler');
$translation = $kernel->getTranslation('administration');

if(isset($_GET['return_redirect_id'])) {
	$redirect = Redirect::factory($kernel, 'return');
	$file_id = $redirect->getParameter('file_handler_id');

	$intranet = new IntranetAdministration($kernel);
	$filehandler = new FileHandler($kernel, intval($file_id));
	if($filehandler->get('id') != 0) {

		$type = $filehandler->get('file_type');
		if($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
			$values = $intranet->get();
			$values['pdf_header_file_id'] = $filehandler->get('id');
			$intranet->update($values);
		}
		else {
			$filehandler->error->set('Header should be a .jpg image - got '. $filehandler->get('file_type'));
		}
	}
}


if(isset($_POST['submit']) || isset($_POST['choose_file'])) {

	$intranet = new IntranetAdministration($kernel);
	$values = $_POST;

	$filehandler = new FileHandler($kernel);
	$filehandler->loadUpload();
	if($id = $filehandler->upload->upload('new_pdf_header_file')) {
		$filehandler->load();

		$type = $filehandler->get('file_type');
		if($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
			$values['pdf_header_file_id'] = $id;
		}
		else {
			$intranet->error->set('Header should be a .jpg image - got ' . $type['mime_type']);
			$filehandler->delete();
		}
	}

	if($intranet->update($values)) {
		$values['name'] = $_POST['address_name'];
		if($intranet->address->save($values)) {
			if(isset($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
				$module_filemanager = $kernel->useModule('filemanager');
				$redirect = Redirect::factory($kernel, 'go');
	 			$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1');
				$redirect->askParameter('file_handler_id');
				header('Location: ' . $url);
				exit;
			}
			else {
				header('Location: '.PATH_WWW . '/main/controlpanel/intranet.php');
			}
		}
	}
	else {
		$values = $_POST;
		$address = $_POST;
	}
}
else {
	$intranet = new IntranetAdministration($kernel);
	$values = $intranet->get();
	$address = $intranet->address->get();
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit intranet')));
?>

<h1><?php echo safeToHtml($translation->get('edit intranet')); ?></h1>

<?php echo $intranet->error->view(); ?>
<?php if(isset($filehandler)) echo $filehandler->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('information about the intranet')); ?></legend>
		<div class="formrow">
			<label for="name"><?php echo safeToHtml($translation->get('name')); ?></label>
			<input type="text" name="name" id="name" value="<?php if(isset($values['name'])) echo safeToHtml($values["name"]); ?>" size="50" />

  		</div>
  		<div class="formrow">
			<label for="identifier"><?php echo safeToHtml($translation->get('identifier', 'common')); ?></label>
			<input type="text" name="identifier" id="identifier" value="<?php if(isset($values['identifier'])) echo safeToHtml($values["identifier"]); ?>" size="50" />

		</div>

	</fieldset>



	<fieldset>
		<legend><?php echo safeToHtml($translation->get('address information')); ?></legend>
		<div class="formrow">
			<label for="address_name"><?php echo safeToHtml($translation->get('name', 'address')); ?></label>
			<input type="text" name="address_name" id="address_name" value="<?php if(isset($address['name'])) echo safeToHtml($address["name"]); ?>" />
		</div>
		<div class="formrow">
			<label for="address"><?php echo safeToHtml($translation->get('address', 'address')); ?></label>
			<textarea name="address" id="address" rows="2"><?php if(isset($address['address'])) echo safeToHtml($address["address"]); ?></textarea>
		</div>
		<div class="formrow">
			<label for="postcode"><?php echo safeToHtml($translation->get('postal code and city', 'address')); ?></label>
			<div>
				<input type="text" name="postcode" id="postcode" value="<?php if(isset($address['postcode'])) echo safeToHtml($address["postcode"]); ?>" size="4" />
				<input type="text" name="city" id="city" value="<?php if(isset($address['city'])) echo safeToHtml($address["city"]); ?>" />
			</div>
		</div>
		<div class="formrow">
			<label for="country"><?php echo safeToHtml($translation->get('country', 'address')); ?></label>
			<input type="text" name="country" id="country" value="<?php if(isset($address['country'])) echo safeToHtml($address["country"]); ?>" />
		</div>
		<div class="formrow">
			<label for="cvr"><?php echo safeToHtml($translation->get('cvr number', 'address')); ?></label>
			<input type="text" name="cvr" id="cvr" value="<?php if(isset($address['cvr'])) echo safeToHtml($address["cvr"]); ?>" />
		</div>
		<div class="formrow">
			<label for="email"><?php echo safeToHtml($translation->get('e-mail', 'address')); ?></label>
			<input type="text" name="email" id="email" value="<?php if(isset($address['email'])) echo safeToHtml($address["email"]); ?>" />
		</div>
		<div class="formrow">
			<label for="website"><?php echo safeToHtml($translation->get('website', 'address')); ?></label>
			<input type="text" name="website" id="website" value="<?php if(isset($address['website'])) echo safeToHtml($address["website"]); ?>" />
		</div>
		<div class="formrow">
			<label for="phone"><?php echo safeToHtml($translation->get('phone', 'address')); ?></label>
			<input type="text" name="phone" id="phone" value="<?php if(isset($address['phone'])) echo safeToHtml($address["phone"]); ?>" />
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo safeToHtml($translation->get('header for pdf')); ?></legend>
		<?php
		// print($intranet->get('pdf_header_file_id')."gg");
		$filehandler = new FileHandler($kernel, $intranet->get('pdf_header_file_id'));
		$filehandler_html = new FileHandlerHTML($filehandler);
		$filehandler_html->printFormUploadTag('pdf_header_file_id','new_pdf_header_file', 'choose_file', array('image_size' => 'small'));
		?>
		<p><?php echo safeToHtml($translation->get('Header should be a .jpg image. For best results make the picture 150px tall')); ?></p>
	</fieldset>






	<div style="clear:both;">
		<input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" /> <a href="/main/controlpanel/intranet.php"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
	</div>
</form>

<?php
$page->end();
?>