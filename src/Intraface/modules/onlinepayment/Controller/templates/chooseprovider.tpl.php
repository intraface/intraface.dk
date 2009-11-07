<h1><?php e(t('Choose provider')); ?></h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

	<fieldset>
		<legend><?php e(t('Provider')); ?></legend>
		<div class="formrow">
			<label for="provider"><?php e(t('Provider')); ?></label>
			<select name="provider_key" id="provider">
				<option value=""><?php e(t('Choose')); ?></option>
				    <?php
					$implemented_providers = OnlinePayment::getImplementedProviders();
                    foreach ($implemented_providers AS $key => $provider):
						if ($provider == '_invalid_') continue;
						echo '<option value="'.$key.'"';
						if (intval($value['provider_key']) == $key):
							echo ' selected="selected"';
						endif;
						echo '>'.$provider.'</option>';
					endforeach;
				    ?>
			</select>
		</div>
	</fieldset>

	<div>
		<input type="submit" value="<?php e(t('Save')); ?>" />
	</div>

</form>
