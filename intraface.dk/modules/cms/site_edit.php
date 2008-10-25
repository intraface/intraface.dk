<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_POST)) {
	$cmssite = new CMS_Site($kernel, (int)$_POST['id']);
	if ($cmssite->save($_POST)) {
		header('Location: site.php?id='.$cmssite->get('id'));
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

$page = new Intraface_Page($kernel);
$page->start($translation->get('edit site'));
?>

<h1><?php e($translation->get('edit site')); ?></h1>

<?php echo $cmssite->error->view($translation); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="POST">
	<fieldset>
		<legend><?php e($translation->get('info about the site')); ?></legend>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
		<div class="formrow">
			<label><?php e($translation->get('website name')); ?></label>
			<input type="text" name="name" size="30" value="<?php if (!empty($value['name'])) e($value['name']); ?>" />
		</div>
		<div class="formrow">
			<label><?php e($translation->get('website url')); ?></label>
			<input type="text" name="url" size="30" value="<?php if (!empty($value['url'])) e($value['url']); ?>" /> <?php e($translation->get('start url with http://')); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php e($translation->get('creative commons license', 'cc_license')); ?></legend>
		<label for="cc-license"><?php e($translation->get('creative commons license', 'cc_license')); ?></label>
		<select name="cc_license" id="cc-license">
			<?php
				foreach ($cms_module->getSetting('cc_license') AS $key=>$license): ?>
					<option value="<?php e($key); ?>" title="<?php e($license['identifier']); ?>"
					<?php if (!empty($value['cc_license']) AND $value['cc_license'] == $key) {
						echo ' selected="selected"';
					}
                    ?>
					><?php e($translation->get($license['identifier'], 'cc_license')); ?></option>
				<?php endforeach;
			?>
		</select>

		<p><a href="http://creativecommons.org/about/licenses/meet-the-licenses"><?php e($translation->get('read more about creative commons licenses', 'cc_license')); ?></a></p>
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
		<input type="submit" class="save" value="<?php e($translation->get('save', 'common')); ?>" /> <?php e($translation->get('or', 'common')); ?> <a href="index.php"><?php e($translation->get('Cancel', 'common')); ?></a>
	</div>
</form>

<?php
$page->end();
?>