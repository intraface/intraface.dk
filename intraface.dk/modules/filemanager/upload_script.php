<?php
require('../../include_first.php');

$module = $kernel->module("filemanager");
$translation = $kernel->getTranslation('filemanager');

if(!empty($_FILES)) {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
			<head>
				<script>
					var par = window.parent.document;
					var images = par.getElementById('images');
					var imgdiv = images.getElementsByTagName('div')[<?php echo (int)$_POST['imgnum']; ?>];
					var image = imgdiv.getElementsByTagName('img')[0];
					imgdiv.removeChild(image);
					var image_new = par.createElement('img');
					image_new.width = '75';
					image_new.height = '75';
					var br_new = par.createElement('br');

					<?php
					$filemanager = new FileManager($kernel);
					$filemanager->loadUpload();
					$filemanager->upload->setSetting('file_accessibility', 'public');
					$filemanager->upload->setSetting('max_file_size', '2000000');
					if ($filemanager->upload->upload('file', 'temporary')) {
						$filemanager->load();
						?>
						var input_new = par.createElement('input');
						// input_new.setAttribute("value", <?php echo $filemanager->get('id'); ?>); // Ser herunder
						input_new.setAttribute("name", 'addfile[]');
						input_new.setAttribute("type", 'checkbox');
						input_new.setAttribute("id", "input-test");


						image_new.src = '<?php echo $filemanager->get('icon_uri'); ?>';
						image_new.className = 'loaded';
						imgdiv.appendChild(image_new);
						imgdiv.appendChild(br_new);
						imgdiv.appendChild(input_new);

						/* IE HACK */
						// IE forstår ikke set attribute, så derfor må vi gøre det uden for DOM bagefter!
						input_new.checked = true;
						input_new.value = <?php echo $filemanager->get('id'); ?>;

						<?php
					}
					else {
						?>
						image_new.src = '<?php echo PATH_WWW.'images/upload_error.jpg'; ?>';
						image_new.className = 'loaded';
						imgdiv.appendChild(image_new);
						imgdiv.appendChild(br_new);
						alert("<?php echo addslashes(implode(". ", $filemanager->error->getMessage())); ?>");
						<?php
					}
					?>
				</script>
			</head>
		</html>
	<?php
	exit();
}
else {
	$filemanager = new FileManager($kernel);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
	<head>
		<script language="javascript">
function upload(){
	// hide old iframe
	var par = window.parent.document;
	var num = par.getElementsByTagName('iframe').length - 1;
	var iframe = par.getElementsByTagName('iframe')[num];

	iframe.className = 'hidden';

	// create new iframe
	var new_iframe = par.createElement('iframe');
	new_iframe.src = 'upload_script.php';
	new_iframe.frameBorder = '0';
	par.getElementById('iframe').appendChild(new_iframe);

	// add image progress
	var images = par.getElementById('images');
	var new_div = par.createElement('div');
	var new_img = par.createElement('img');
	new_img.src = '<?php print(PATH_WWW.'images/indicator.gif'); ?>';
	new_img.className = 'load';
	new_div.appendChild(new_img);
	images.appendChild(new_div);

	// send
	var imgnum = images.getElementsByTagName('div').length - 1;
	document.iform.imgnum.value = imgnum;

	setTimeout('document.iform.submit()', 5000);
}
</script>
<style>
.formrow label {
	width: 8em;
	float: left;
	font-family: Arial, Verdana, sans-serif;
	font-size: small;
}
.formrow  {
	clear: both;
}

form {
	width: 90%;
}

legend {
	font-weight: bold;
	margin-bottom: 0.5em;
}

fieldset{
	padding: 0.5em;
}
body {
	width: 90%;
	margin: 0;
	padding: 0;
}
</style>
</head>
<body>
		<form name="iform" action="upload_script.php" method="post" enctype="multipart/form-data">
			<div class="formrow">
				<label><?php echo safeToHtml($translation->get('file')); ?></label>
				<input id="file" type="file" name="file" onchange="upload()" /><input type="hidden" name="imgnum" />
			</div>
		</form>
</html>