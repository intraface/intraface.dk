<?php
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

// opdater regnskabs�ret
if (!empty($_POST)) {
	$year = new Year($kernel, (int)$_POST['id'], false);
	if ($id = $year->save($_POST)) {
		header('Location: year.php?id='.$id);
		exit;
	}
	else {
		$values = $_POST;
		$values['from_date_dk'] = $_POST['from_date'];
		$values['to_date_dk'] = $_POST['to_date'];
	}
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$year = new Year($kernel, (int)$_GET['id']);
	$values = $year->get();
}
else {
	$year = new Year($kernel);
	$values['from_date_dk'] = '01-01-' . date('Y');
	$values['to_date_dk'] = '31-12-' . date('Y');
}
$years = $year->getList();
$account = new Account($year);

$page = new Intraface_Page($kernel);

$page->start('Rediger regnskab');
?>
<h1>Regnskabs�r</h1>


<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
	<input type="hidden" name="id" value="<?php if (!empty($values['id'])) echo intval($values['id']); ?>" />

	<?php echo $year->error->view(); ?>

	<fieldset>
		<legend>Oplysninger om regnskabs�ret</legend>

		<div class="formrow">
			<label for="label">Navn</label>
			<input type="text" name="label" id="label" value="<?php if (!empty($values['label'])) echo safeToHtml($values['label']); ?>" />
		</div>

		<div class="formrow">
			<label for="from_date">Fra dato</label>
			<input type="text" name="from_date" id="from_date" value="<?php if (!empty($values['from_date_dk'])) echo safeToHtml($values['from_date_dk']); ?>" />
		</div>

		<div class="formrow">
			<label for="to_date">Til dato</label>
			<input type="text" name="to_date" id="to_date" value="<?php if (!empty($values['to_date_dk'])) echo safeToHtml($values['to_date_dk']); ?>" />
		</div>
		<br /> <!-- M�rkelig nok skal denne v�re der for det ser ordentlig ud!!! /Sune -->
		<div class="formrow">
			<label for="last_year_id">Sidste �rs regnskab</label>
			<select name="last_year_id" id="last_year_id">
					<option value="0">Ingen</option>
					<?php
					foreach ($years AS $y):
						if (!empty($values['id']) AND $y['id'] == $values['id']) { continue; }
						?>
						<option value="<?php echo $y['id']; ?>"<?php if (!empty($values['last_year_id']) AND $y['id'] == $values['last_year_id']) { echo ' selected="selected"'; } ?>><?php echo $y['label']; ?></option>
					<?php endforeach; ?>
			</select>
		</div>

		<div class="formrow">
			<label for="locked">L�st</label>
			<select name="locked" id="locked">
					<option value="0"<?php if (!empty($values['locked']) AND $values['locked'] == '0') { echo ' selected="selected"'; } ?>>Nej</option>
					<option value="1"<?php if (!empty($values['locked']) AND $values['locked'] == '1') { echo ' selected="selected"'; } ?>>Ja</option>
			</select>
		</div>

		<div class="formrow">
			<label for="vat">Moms</label>
			<input type="checkbox" name="vat" id="vat" value="1" <?php if (!empty($values['vat']) AND $values['vat'] == 1) echo ' checked="checked"';  ?>/>
		</div>

	<div style="clear:both;">
		<input type="submit" value="Gem" name="submit" id="submit" />
		eller
		<a href="year.php?id=<?php if (!empty($values['id'])) echo intval($values['id']); ?>">Fortryd</a>
	</div>
	</fieldset>
</form>

<?php
$page->end();
?>