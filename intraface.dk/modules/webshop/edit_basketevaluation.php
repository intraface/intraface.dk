<?php
require('../../include_first.php');

$webshop_module = $kernel->module('webshop');
$translation = $kernel->getTranslation('webshop');

$webshop_module->includeFile('BasketEvaluation.php');

if (!empty($_POST)) {
	$basketevaluation = new BasketEvaluation($kernel, (int)$_POST['id']);
	
	if($basketevaluation->save($_POST)) {
		header("Location: index.php");
		exit;
	}
	else {
		$value = $_POST;
	}
	
}
elseif(isset($_GET['id'])) {
	
	$basketevaluation = new BasketEvaluation($kernel, (int)$_GET['id']);
	$value = $basketevaluation->get();
}
else {
	$basketevaluation = new BasketEvaluation($kernel);
	$value = array();
}

$settings = $basketevaluation->get('settings');

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('basket evaluation')));

?>
<h1><?php echo safeToHtml($translation->get('basket evaluation')); ?></h1>


<?php echo $basketevaluation->error->view($translation); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST">
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('information')); ?></legend>
		<input type="hidden" name="id" value="<?php if (!empty($value['id'])) echo intval($value['id']); ?>" />
		
		<div class="formrow">
			<label for="running_index"><?php echo safeToHtml($translation->get('index')); ?></label>
			<input type="text" name="running_index" size="6" value="<?php if (!empty($value['running_index'])) echo safeToForm($value['running_index']); ?>" /> <?php echo safeToHtml($translation->get('Number that decides the order for the evaluation')); ?>
		</div>
	</fieldset>
	
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('evaluation')); ?></legend>
		
		<div class="formrow">
			<label for="evaluate_target_key"><?php echo safeToHtml($translation->get('evaluation target')); ?></label>
			<select name="evaluate_target_key">
				<?php foreach($settings['evaluate_target'] AS $key => $evaluate_target): ?>
					<option value="<?php print(intval($key)); ?>" <?php if (!empty($value['evaluate_target_key']) && $value['evaluate_target_key'] == $key) echo 'selected="selected"'; ?> ><?php echo safeToHtml($translation->get($evaluate_target)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="evaluate_method_key"><?php echo safeToHtml($translation->get('evaluation method')); ?></label>
			<select name="evaluate_method_key">
				<?php foreach($settings['evaluate_method'] AS $key => $evaluate_method): ?>
					<option value="<?php print(intval($key)); ?>" <?php if (!empty($value['evaluate_method_key']) && $value['evaluate_method_key'] == $key) echo 'selected="selected"'; ?> ><?php echo safeToHtml($translation->get($evaluate_method)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="evaluate_value"><?php echo safeToHtml($translation->get('evaluation value')); ?></label>
			<input type="text" name="evaluate_value" size="10" value="<?php if (!empty($value['evaluate_value'])) echo safeToForm($value['evaluate_value']); ?>" />
		</div>
        
        <div class="formrow">
            <label for="evaluate_value_case_sensitive"><?php echo safeToHtml($translation->get('case sensitive')); ?></label>
            <input type="checkbox" name="evaluate_value_case_sensitive" value="1" <?php if (!empty($value['evaluate_value_case_sensitive']) && (int)$value['evaluate_value_case_sensitive'] == 1) echo 'checked="checked"'; ?> />
        </div>
		
		<div class="formrow">
			<label for="go_to_index_after"><?php echo safeToHtml($translation->get('go to index after')); ?></label>
			<input type="text" name="go_to_index_after" size="6" value="<?php if (isset($value['go_to_index_after'])) echo safeToForm($value['go_to_index_after']); ?>" />
		</div>
		
	</fieldset>
	
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('action')); ?></legend>
		
		<div class="formrow">
			<label for="action_action_key"><?php echo safeToHtml($translation->get('action')); ?></label>
			<select name="action_action_key">
				<?php foreach($settings['action_action'] AS $key => $action_action): ?>
					<option value="<?php print(intval($key)); ?>" <?php if (!empty($value['action_action_key']) && $value['action_action_key'] == $key) echo 'selected="selected"'; ?> ><?php echo safeToHtml($translation->get($action_action)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="formrow">
			<label for="action_value"><?php echo safeToHtml($translation->get('target')); ?></label>
			<input type="text" name="action_value" size="30" value="<?php if (!empty($value['action_value'])) echo safeToForm($value['action_value']); ?>" />
		</div>
		
		<div class="formrow">
			<label for="action_quantity"><?php echo safeToHtml($translation->get('quantity')); ?></label>
			<input type="text" name="action_quantity" size="30" value="<?php if (!empty($value['action_quantity'])) echo safeToForm($value['action_quantity']); ?>" />
		</div>
		
		<div class="formrow">
			<label for="action_unit_key"><?php echo safeToHtml($translation->get('action unit')); ?></label>
			<select name="action_unit_key">
				<?php foreach($settings['action_unit'] AS $key => $action_unit): ?>
					<option value="<?php print(intval($key)); ?>" <?php if (!empty($value['action_unit_key']) && $value['action_unit_key'] == $key) echo 'selected="selected"'; ?> ><?php echo safeToHtml($translation->get($action_unit)); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>

	<input type="submit" class="save" name="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" /> <?php echo safeToHtml($translation->get('or', 'common')); ?> <a href="index.php"><?php echo safeToHtml($translation->get('cancel', 'common')); ?></a>
</form>

<?php
$page->end();
?>
