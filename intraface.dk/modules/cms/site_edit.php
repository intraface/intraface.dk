<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_POST)) {
	$cmssite = new CMS_Site($kernel, (int)$_POST['id']);
	if ($cmssite->save($_POST)) {
		header('Location: index.php');
		exit;
	}
	else {
		$value = $_POST;
	}
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$cmssite = new CMS_Site($kernel, (int)$_GET['id']);
	$value = $cmssite->get();
}
else {
	$cmssite = new CMS_Site($kernel);
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('edit site')));
?>

<h1><?php echo safeToHtml($translation->get('edit site')); ?></h1>

<?php
	$cmssite->error->view($translation);
?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('info about the site')); ?></legend>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) echo intval($value['id']); ?>" />
		<div class="formrow">
			<label><?php echo safeToHtml($translation->get('website name')); ?></label>
			<input type="text" name="name" size="30" value="<?php if (!empty($value['name'])) echo safeToForm($value['name']); ?>" />
		</div>
		<div class="formrow">
			<label><?php echo safeToHtml($translation->get('website url')); ?></label>
			<input type="text" name="url" size="30" value="<?php if (!empty($value['url'])) echo safeToForm($value['url']); ?>" /> <?php echo $translation->get('start url with http://'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo safeToHtml($translation->get('creative commons license', 'cc_license')); ?></legend>
		<label for="cc-license"><?php echo safeToHtml($translation->get('creative commons license', 'cc_license')); ?></label>
		<select name="cc_license" id="cc-license">
			<?php
				foreach ($cms_module->getSetting('cc_license') AS $key=>$license):
					echo '<option value="'.$key.'" title="'.$license['identifier'].'"';
					if (!empty($value['cc_license']) AND $value['cc_license'] == $key) {
						echo ' selected="selected"';
					}
					echo '>'.safeToForm($translation->get($license['identifier'], 'cc_license')).'</option>';
				endforeach;
			?>
		</select>

		<p><a href="http://creativecommons.org/about/licenses/meet-the-licenses"><?php echo safeToHtml($translation->get('read more about creative commons licenses', 'cc_license')); ?></a></p>
		<!--
		<script type="text/javascript">
			var creative_commons = {
				init: function() {
					creative_commons.select = document.getElementById('cc-license');
				}
			}
		</script>
		-->

	</fieldset>

	<div>
		<input type="submit" class="save" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" /> <?php echo safeToHtml($translation->get('or', 'common')); ?> <a href="index.php"><?php echo safeToHtml($translation->get('regret', 'common')); ?></a>
	</div>
</form>

<?php
$page->end();
?>