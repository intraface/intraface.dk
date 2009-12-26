<h1><?php e(t('Delete intranet')); ?></h1>

<form action="<?php e(url()); ?>" method="post">
	<input type="hidden" value="delete" name="_method" />
	<fieldset>
		<legend><?php e(t('Choose intranet')); ?></legend>
		<select name="intranet_id">
			<option value=""><?php e(t('Choose')); ?></option>
			<?php
			foreach ($allowed_delete AS $id=>$intranet):
				echo '<option value="'.$id.'">'.$intranet.'</option>';
			endforeach;
			?>

		</select>
		<input type="submit" value="<?php e(t('Delete')); ?>" />
	</fieldset>

</form>
