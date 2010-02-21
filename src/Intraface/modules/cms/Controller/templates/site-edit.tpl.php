<h1><?php e(t('edit site')); ?></h1>
<?php echo $cmssite->error->view(array($context, 't')); ?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="POST">
	<fieldset>
		<legend><?php e(t('info about the site')); ?></legend>
		<div class="formrow">
			<label><?php e(t('website name')); ?></label>
			<input type="text" name="name" size="30" value="<?php if (!empty($value['name'])) e($value['name']); ?>" />
		</div>
		<div class="formrow">
			<label><?php e(t('website url')); ?></label>
			<input type="text" name="url" size="30" value="<?php if (!empty($value['url'])) e($value['url']); ?>" /> <?php e(t('start url with http://')); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php e(t('creative commons license', 'cc_license')); ?></legend>
		<label for="cc-license"><?php e(t('creative commons license', 'cc_license')); ?></label>
		<select name="cc_license" id="cc-license">
			<?php
				foreach ($cms_module->getSetting('cc_license') AS $key=>$license): ?>
					<option value="<?php e($key); ?>" title="<?php e($license['identifier']); ?>"
					<?php if (!empty($value['cc_license']) AND $value['cc_license'] == $key) {
						echo ' selected="selected"';
					}
                    ?>
					><?php e(t($license['identifier'], 'cc_license')); ?></option>
				<?php endforeach;
			?>
		</select>

		<p><a href="http://creativecommons.org/about/licenses/meet-the-licenses"><?php e(t('read more about creative commons licenses', 'cc_license')); ?></a></p>
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
		<input type="submit" class="save" value="<?php e(t('save')); ?>" />
		<a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
	</div>
</form>