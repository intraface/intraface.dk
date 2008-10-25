<?php
require('../../include_first.php');

$webshop_module = $kernel->module('webshop');
$translation = $kernel->getTranslation('webshop');

$webshop_module->includeFile('BasketEvaluation.php');

if (!empty($_POST)) {
	$basketevaluation = new BasketEvaluation($kernel, (int)$_POST['id']);
	
	if ($basketevaluation->save($_POST)) {
		header("Location: index.php");
		exit;
	}
	else {
		$value = $_POST;
	}
	
}
elseif (isset($_GET['id'])) {
	
	$basketevaluation = new BasketEvaluation($kernel, (int)$_GET['id']);
	$value = $basketevaluation->get();
}
else {
	$basketevaluation = new BasketEvaluation($kernel);
	$value = array();
}

$settings = $basketevaluation->get('settings');

$page = new Intraface_Page($kernel);
$page->start($translation->get('basket evaluation'));

?>
<h1><?php e($translation->get('basket evaluation')); ?></h1>


<?php echo $basketevaluation->error->view($translation); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="POST">
	<fieldset>
		<legend><?php e($translation->get('information')); ?></legend>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
		
		<div class="formrow">
			<label for="running_index"><?php e($translation->get('index')); ?></label>
			<input type="text" name="running_index" size="6" value="<?php if (!empty($value['running_index'])) e($value['running_index']); ?>" /> <?php e($translation->get('Number that decides the order for the evaluation')); ?>
		</div>
	</fieldset>
	
	<fieldset>
		<legend><?php e($translation->get('evaluation')); ?></legend>
		
		<div class="formrow">
			<label for="evaluate_target_key"><?php e($translation->get('evaluation target')); ?></label>
			<select name="evaluate_target_key">
				<?php foreach ($settings['evaluate_target'] AS $key => $evaluate_target): ?>
					<option value="<?php e($key); ?>" <?php if (!empty($value['evaluate_target_key']) && $value['evaluate_target_key'] == $key) echo 'selected="selected"'; ?> ><?php e($translation->get($evaluate_target)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="evaluate_method_key"><?php e($translation->get('evaluation method')); ?></label>
			<select name="evaluate_method_key">
				<?php foreach ($settings['evaluate_method'] AS $key => $evaluate_method): ?>
					<option value="<?php e($key); ?>" <?php if (!empty($value['evaluate_method_key']) && $value['evaluate_method_key'] == $key) echo 'selected="selected"'; ?> ><?php e($translation->get($evaluate_method)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="evaluate_value"><?php e($translation->get('evaluation value')); ?></label>
			<input type="text" name="evaluate_value" size="10" value="<?php if (!empty($value['evaluate_value'])) e($value['evaluate_value']); ?>" />
		</div>
        
        <div class="formrow">
            <label for="evaluate_value_case_sensitive"><?php e($translation->get('case sensitive')); ?></label>
            <input type="checkbox" name="evaluate_value_case_sensitive" value="1" <?php if (!empty($value['evaluate_value_case_sensitive']) && (int)$value['evaluate_value_case_sensitive'] == 1) echo 'checked="checked"'; ?> />
        </div>
		
		<div class="formrow">
			<label for="go_to_index_after"><?php e($translation->get('go to index after')); ?></label>
			<input type="text" name="go_to_index_after" size="6" value="<?php if (isset($value['go_to_index_after'])) e($value['go_to_index_after']); ?>" />
		</div>
		
	</fieldset>
	
	<fieldset>
		<legend><?php e($translation->get('action')); ?></legend>
		
		<div class="formrow">
			<label for="action_action_key"><?php e($translation->get('action')); ?></label>
			<select name="action_action_key">
				<?php foreach ($settings['action_action'] AS $key => $action_action): ?>
					<option value="<?php e($key); ?>" <?php if (!empty($value['action_action_key']) && $value['action_action_key'] == $key) echo 'selected="selected"'; ?> ><?php e($translation->get($action_action)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="action_value"><?php e($translation->get('target')); ?></label>
			<input type="text" name="action_value" size="30" value="<?php if (!empty($value['action_value'])) e($value['action_value']); ?>" />
		</div>
		
		<div class="formrow">
			<label for="action_quantity"><?php e($translation->get('quantity')); ?></label>
			<input type="text" name="action_quantity" size="30" value="<?php if (!empty($value['action_quantity'])) e($value['action_quantity']); ?>" />
		</div>
		
		<div class="formrow">
			<label for="action_unit_key"><?php e($translation->get('action unit')); ?></label>
			<select name="action_unit_key">
				<?php foreach ($settings['action_unit'] as $key => $action_unit): ?>
					<option value="<?php e($key); ?>" <?php if (!empty($value['action_unit_key']) && $value['action_unit_key'] == $key) echo 'selected="selected"'; ?> ><?php e($translation->get($action_unit)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<input type="submit" class="save" name="submit" value="<?php e($translation->get('save', 'common')); ?>" /> <?php e($translation->get('or', 'common')); ?> <a href="index.php"><?php e($translation->get('cancel', 'common')); ?></a>
</form>

<?php
$page->end();
?>
