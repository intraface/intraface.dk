<h1><?php e(__('edit site')); ?></h1>

<?php echo $cmssite->error->view($translation); ?>

<form action="<?php e(url()); ?>" method="POST">
	<fieldset>
		<legend><?php e(__('info about the site')); ?></legend>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
		<div class="formrow">
			<label><?php e(__('website name')); ?></label>
			<input type="text" name="name" size="30" value="<?php if (!empty($value['name'])) e($value['name']); ?>" />
		</div>
		<div class="formrow">
			<label><?php e(__('website url')); ?></label>
			<input type="text" name="url" size="30" value="<?php if (!empty($value['url'])) e($value['url']); ?>" /> <?php e(__('start url with http://')); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php e(__('creative commons license', 'cc_license')); ?></legend>
		<label for="cc-license"><?php e(__('creative commons license', 'cc_license')); ?></label>
		<select name="cc_license" id="cc-license">
			<?php
				foreach ($cms_module->getSetting('cc_license') AS $key=>$license): ?>
					<option value="<?php e($key); ?>" title="<?php e($license['identifier']); ?>"
					<?php if (!empty($value['cc_license']) AND $value['cc_license'] == $key) {
						echo ' selected="selected"';
					}
                    ?>
					><?php e(__($license['identifier'], 'cc_license')); ?></option>
				<?php endforeach;
			?>
		</select>

		<p><a href="http://creativecommons.org/about/licenses/meet-the-licenses"><?php e(__('read more about creative commons licenses', 'cc_license')); ?></a></p>
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
		<input type="submit" class="save" value="<?php e(__('save', 'common')); ?>" />
		<a href="<?php e(url('../')); ?>"><?php e(__('Cancel', 'common')); ?></a>
	</div>
</form>