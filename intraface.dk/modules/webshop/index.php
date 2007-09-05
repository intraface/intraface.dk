<?php
require('../../include_first.php');

$webshop_module = $kernel->module('webshop');
$settings = $webshop_module->getSetting('show_online');
$translation = $kernel->getTranslation('webshop');
$webshop_module->includeFile('BasketEvaluation.php');

$error = new Error();

if (!empty($_POST)) {
	// mangler validering

	$validator = new Validator($error);
	$validator->isNumeric($_POST['show_online'], 'show_online skal være et tal');
	//$validator->isNumeric($_POST['discount_limit'], 'discount_limit skal være et tal');
	//$validator->isNumeric($_POST['discount_percent'], 'discount_percent skal være et tal');
	$validator->isString($_POST['confirmation_text'], 'confirmation text is not valid');
    $validator->isString($_POST['webshop_receipt'], 'webshop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

	if (!$error->isError()) {

		$kernel->setting->set('intranet','webshop.show_online', $_POST['show_online']);
		//$kernel->setting->set('intranet','webshop.discount_limit', $_POST['discount_limit']);
		//$kernel->setting->set('intranet','webshop.discount_percent', $_POST['discount_percent']);
		$kernel->setting->set('intranet','webshop.confirmation_text', $_POST['confirmation_text']);
		$kernel->setting->set('intranet','webshop.webshop_receipt', $_POST['webshop_receipt']);
		
		header('Location: index.php');
		exit;
	}
	else {
		$value = $_POST;
	}
}
else {
	$value['discount_limit'] = $kernel->setting->get('intranet','webshop.discount_limit');
	$value['discount_percent'] = $kernel->setting->get('intranet','webshop.discount_percent');
	$value['show_online'] = $kernel->setting->get('intranet','webshop.show_online');
	$value['confirmation_text'] = $kernel->setting->get('intranet','webshop.confirmation_text');
	$value['webshop_receipt'] = $kernel->setting->get('intranet','webshop.webshop_receipt');
}

if(isset($_GET['delete_basketevaluation_id'])) {
	$basketevaluation = new BasketEvaluation($kernel, $_GET['delete_basketevaluation_id']);
	$basketevaluation->delete();
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('webshop')));

?>
<h1><?php echo safeToHtml($translation->get('webshop')); ?></h1>

<p class="message">
	<?php echo safeToHtml($translation->get('here you edit your settings for the webshop')); ?>
</p>

<form action="<?php basename($_SERVER['PHP_SELF']); ?>" method="post">

	<?php echo $error->view(); ?>

	<fieldset>
		<legend><?php echo safeToHtml($translation->get('what to show in the webshop')); ?></legend>
		<div class="formrow">
		<label>Vis</label>

			<select name="show_online">
			<?php
				foreach($settings AS $k=>$v) {
					echo '<option value="'.$k.'"';
					if (!empty($value['show_online']) AND $k == $value['show_online']) echo ' selected="selected"';
					echo '>' . safeToForm($translation->get($v)) . '</option>';
				}
			?>
			</select>
		</div>
	</fieldset>
	<!--
	<fieldset>
		<legend>Rabat</legend>
		<div class="formrow">
		<label>Rabatgrænse</label>
		<input value="<?php echo safeToHtml($value['discount_limit']); ?>" name="discount_limit" type="text" /> kroner
		</div>
		<div class="formrow">
		<label>Rabat</label>
		<input value="<?php echo safeToHtml($value['discount_percent']); ?>" name="discount_percent" type="text" /> %

		</div>
	</fieldset>
	-->
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('order confirmation - including warranty and right of cancellation')); ?></legend>
		<div>
		<label for="confirmation_text"><?php echo safeToHtml($translation->get('text')); ?></label><br />
		<textarea name="confirmation_text" cols="80" rows="10"><?php echo safeToForm($value['confirmation_text']); ?></textarea>
		</div>
	</fieldset>
	
	<fieldset>
		<legend><?php echo safeToHtml($translation->get('webshop receipt')); ?></legend>
		<div>
		<label for="webshop_receipt"><?php echo safeToHtml($translation->get('text')); ?></label><br />
		<textarea name="webshop_receipt" cols="80" rows="10"><?php echo safeToForm($value['webshop_receipt']); ?></textarea>
		</div>
	</fieldset>

	<p>
		<input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" />
	</p>

</form>

<fieldset>
	<legend><?php echo safeToHtml($translation->get('Basket evaluation')); ?></legend>
	
	
	
	<?php
	$basketevaluation = new BasketEvaluation($kernel);
	$evaluations = $basketevaluation->getList();
	
	if(count($evaluations) > 0):
		?>
		<table summary="<?php echo safeToHtml($translation->get('basket evaluation')); ?>" class="stripe">
			<caption><?php echo safeToHtml($translation->get('basket evaluation')); ?></caption>
			<thead>
				<tr>
					<th><?php echo safeToHtml($translation->get('running index')); ?></th>
					<th><?php echo safeToHtml($translation->get('evaluation')); ?></th>
					<th><?php echo safeToHtml($translation->get('action')); ?></th>
                    <th><?php echo safeToHtml($translation->get('go to index after')); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($evaluations AS $evaluation): ?>
					<tr>
						<td><?php echo safeToHtml($evaluation['running_index']); ?></td>
						<td><?php echo safeToHtml($translation->get('if').' '.$translation->get($evaluation['evaluate_target']).' '.$translation->get('is').' '.$translation->get($evaluation['evaluate_method']).' '.$evaluation['evaluate_value']); ?></td>
						<td><?php echo safeToHtml($translation->get($evaluation['action_action']).' '.$evaluation['action_value'].' '.$translation->get('at').' '.$evaluation['action_quantity'].' '.$translation->get($evaluation['action_unit'])); ?></td>
						<td><?php echo safeToHtml($evaluation['go_to_index_after']); ?></td>
                        <td><a href="edit_basketevaluation.php?id=<?php echo intval($evaluation['id']); ?>" class="edit"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a> <a href="index.php?delete_basketevaluation_id=<?php echo intval($evaluation['id']); ?>" class="delete"><?php echo safeToHtml($translation->get('delete', 'common')); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	endif;
	?>					
	
	<p><a href="edit_basketevaluation.php"><?php echo safeToHtml($translation->get('add basket evaluation')); ?></a></p>
		
</fieldset>

<?php
$page->end();
?>