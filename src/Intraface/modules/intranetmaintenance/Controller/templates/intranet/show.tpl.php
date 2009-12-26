<?php
$value = $context->getValues();
$address_value = $context->getValues();
?>
<?php if (!empty($contact_error)): ?>
<?php echo $contact_error;?>
<?php else: ?>
<div id="colOne">

<h1><?php e(__('Intranet')); ?>: <?php e($context->getIntranet()->get('name')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(__('edit', 'common')); ?></a></li>
	<li><a href="<?php e(url('../', array('use_stored' => true))); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

<?php if ($context->query('flare')): ?>
 <p class="message"><?php e(__($context->query('flare')));?></p>
<?php endif; ?>

<?php echo $context->getIntranet()->error->view(); ?>
<?php if (isset($modulepackagemanager)) echo $modulepackagemanager->error->view(); ?>

<table>
	<tr>
		<th><?php e(__('name', 'address')); ?></th>
		<td>
			<?php if (isset($value['name'])) e($value["name"]); ?>
			<?php if (!empty($value['contact_id']) AND $context->getIntranet()->get('id') > 0 && isset($contact_module)): ?>
				<?php
					$contact = new Contact($context->getKernel(), $value['contact_id']);
					echo '<a href="'.$contact_module->getPath() . $contact->get('id').'">'.$contact->get('name').'</a>';
					echo ' <a href="'.url(null, array('add_contact'=>1)).'">'.__('change contact').'</a>';
				?>
			<?php elseif (isset($contact_module)): ?>
				<a href="<?php e(url(null, array('add_contact' => 1))); ?>"><?php e(__('add contact')); ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<!--
	<tr>
		<th><?php e(__('maintained by')); ?></th>
		<td></td>
	</tr>
	-->


	<tr>
		<th><?php e(__('name', 'address')); ?></th>
		<td><?php if (isset($address_value["name"])) e($address_value["name"]); ?></td>
	</tr>

	<tr>
		<th><?php e(__('address', 'address')); ?></th>
		<td><?php if (isset($address_value["address"])) e($address_value["address"]); ?></td>
	</tr>

	<tr>
		<th><?php e(__('postal code and city', 'address')); ?></th>
		<td><?php if (isset($address_value["postcode"])) e($address_value["postcode"]); ?> <?php if (isset($address_value["city"])) e($address_value["city"]); ?></td>
	</tr>
	<tr>
		<th><?php e(__('country', 'address')); ?></th>
		<td><?php if (isset($address_value["country"])) e($address_value["country"]); ?></td>
	</tr>
	<tr>
		<th><?php e(__('cvr number', 'address')); ?></th>
		<td><?php if (isset($address_value["cvr"])) e($address_value["cvr"]); ?></td>
	</tr>
	<tr>
		<th><?php e(__('e-mail', 'address')); ?></th>
		<td><?php if (isset($address_value["email"])) e($address_value["email"]); ?></td>
	</tr>

	<tr>
		<th><?php e(__('website', 'address')); ?></th>
		<td><?php if (isset($address_value["website"])) e($address_value["website"]); ?></td>
	</tr>

	<tr>
		<th><?php e(__('phone', 'address')); ?></th>
		<td><?php if (isset($address_value["phone"])) e($address_value["phone"]); ?></td>
	</tr>

		<tr>
		<th><?php e(__('private key')); ?></th>
		<td><?php e($context->getIntranet()->get("private_key")); ?></td>
	</tr>

	<tr>
		<th><?php e(__('public key')); ?></th>
		<td><?php e($context->getIntranet()->get("public_key")); ?></td>
	</tr>

</table>

<form action="<?php e(url('permission')); ?>" method="post">

<input type="hidden" name="id" value="<?php e($context->getIntranet()->get("id")); ?>" />
<input type="hidden" name="_method" value="put" />
    <?php
    $modulepackagemanager = new Intraface_modules_modulepackage_Manager($context->getIntranet());
    $modulepackagemanager->getDBQuery($context->getKernel());
    $packages = $modulepackagemanager->getList();

    if (count($packages) > 0) {
        ?>
        <table class="stribe">
            <caption>Modulpakker</caption>
            <thead>
                <tr>
                    <th>Modulpakke</th>
                    <th>Startdato</th>
                    <th>Slutdato</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php e($package['plan'].' '.$package['group']); ?></td>
                    <td><?php e($package['start_date']); ?></td>
                    <td><?php e($package['end_date']); ?></td>
                    <td><?php e(__($package['status'])); ?></td>
                    <td><a href="edit_module_package.php?id=<?php e($package['id']); ?>" class="edit">Ret</a> <a href="intranet.php?id=<?php e($context->getIntranet()->get('id')); ?>&amp;delete_intranet_module_package_id=<?php e($package['id']); ?>" class="delete">Slet</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    ?>



<fieldset>
    <legend>Tilføj modulpakke</legend>
    <?php if (!$context->getKernel()->intranet->hasModuleAccess('shop')): ?>
        This intranet needs to have access to shop for modulepackage to work!
    <?php else: ?>

        <?php
        $modulepackage = new Intraface_modules_modulepackage_ModulePackage;
        $modulepackage->getDBQuery($context->getKernel());
        $packages = $modulepackage->getList();
        ?>
        <div class="formrow">
            <label for="module_package_id">Vælg pakke</label>
            <select name="module_package_id" id="module_package_id">
                <?php

                foreach ($packages AS $package) { ?>
                    <option value="<?php e($package['id']); ?>"><?php e($package['plan'].' '.$package['group']); ?></option>
                <?php }
                ?>
            </select>
        </div>


        <div class="formrow">
            <label for="start_date">Start dato</label>
            <input type="text" name="start_date" id="start_date" value="<?php e(date('d-m-Y')); ?>" />
        </div>

        <div class="formrow">
            <label for="duration_month">Varighed i måneder</label>
            <select name="duration_month" id="duration_month">
                <?php
                for ($i = 1; $i < 25; $i++) {
                    echo '<option value="'.intval($i).'">'.intval($i).'</option>';
                }
                ?>
            </select>
        </div>
        <input type="submit" name="add_module_package" value="Tilføj" class="save" />
    <?php endif; ?>

</fieldset>


<fieldset>
	<legend>Adgang til moduler</legend>
	<div>
    <?php

	$module = new ModuleMaintenance;
	$modules = $module->getList();

	foreach ($modules as $module) {
		?>
		<div style="float: left; width: 210px; ">
			<input type="checkbox" name="module[]" id="module_<?php e($module["name"]); ?>" value="<?php e($module["name"]); ?>"<?php if ($context->getIntranet()->hasModuleAccess(intval($module["id"]))) print("checked=\"checked\""); ?> />
			<label for="module_<?php e($module["name"]); ?>"><?php e($module["name"]); ?></label>
		</div>
		<?php
	}
	?>
    </div>
    <div style="clear:both;">
        <input type="submit" name="change_permission" value="<?php e(t('Save')); ?>" />
    </div>
</fieldset>

</form>


</div>

<div id="colTwo">

<table class="stribe">
	<caption><?php e(t('Users')); ?></caption>
	<thead>
	<tr>
		<th><?php e(t('Name')); ?></th>
		<th><?php e(t('Email')); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($context->getUsers() as $user_list) { ?>
		<tr>
			<?php
			if ($user_list['name'] == '') $user_list['name'] = '[not filled in]';
			?>
			<td><a href="<?php e(url('../../user/'. $user_list['id'], array('intranet_id' => $context->getIntranet()->get('id')))); ?>"><?php e($user_list['name']); ?></a></td>
			<td><?php e($user_list['email']); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<p><a href="<?php e(url('../../user', array('create', 'intranet_id' => $context->getIntranet()->get('id')))); ?>">Create new user</a></p>

<p><a href="<?php e(url(null, array('add_user' => 1))); ?>"><?php e(t('Add existing user')); ?></a></p>

</div>
<?php endif; ?>