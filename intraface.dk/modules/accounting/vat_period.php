<?php
/**
 * Momsafregning
 *
 * Denne side skal være en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anført på momskonti.
 *
 * Når man klikker på angiv moms skal tallene gemmes i en database.
 *
 * Hvis man vil redigere tallene, klikker man sig hen til vat_edit.php
 *
 * Siden skal regne ud, om der er forskel på de tal, der er blevet
 * opgivet og det der rent faktisk skulle være opgivet, så man kan fange
 * evt. fejl næste gang man skal opgive moms.
 *
 * Primosaldoer skal naturligvis fremgå af momsopgørelsen.
 *
 * Der skal være en liste med momsangivelsesperioder for året,
 * og så skal der ud for hver momssopgivelse være et link enten til
 * den tidligere opgivne moms eller til at oprette en momsangivelse.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$allowed_periods = $module->getSetting('vat_periods');

$year = new Year($kernel);
$year->checkYear();

if (!empty($_POST['create_periods'])) {
	if (isset($_POST['vat_period_key'])) {
		$year->setSetting('vat_period', $_POST['vat_period_key']);
	}
	$vat_period = new VatPeriod($year);
	$vat_period->createPeriods();
	header('Location: vat_period.php');
	exit;
}
elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
	$vat_period = new VatPeriod($year, $_GET['delete']);
	$vat_period->delete();
}
else {
	$vat_period = new VatPeriod($year);
}

$periods = $vat_period->getList();
$post = new Post(new Voucher($year));

$page = new Page($kernel);
$page->start('Momsoversigt');
?>

<h1>Moms <?php echo $year->get('label'); ?></h1>

<?php echo $vat_period->error->view(); ?>

<?php if ($year->get('vat') == 0): ?>
	<p class="message">Dit regnskab bruger ikke moms, så du kan ikke se momsangivelserne.</p>
<?php elseif (count($post->getList('draft')) > 0): ?>
	<p class="warning">Der er stadig poster i kassekladden. De bør bogføres, inden du opgør momsen. <a href="daybook.php">Gå til kassekladden</a>.</p>
<?php elseif (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php elseif (!$vat_period->periodsCreated()): ?>
	<div class="message">
		<p><strong>Moms</strong>. På denne side kan du få hjælp til at afregne moms. Inden du gør noget, skal du sørge for at alle beløbene for den pågældende periode, er tastet ind i systemet.</p>
	</div>

	<p class="message-dependent">Der er ikke oprettet nogen momsperioder for dette år.</p>
	<form action="<?php basename($_SERVER['PHP_SELF']); ?>" method="post">
		<fieldset>
			<label for="vat_period_key">Hvor ofte skal du afregne moms</label>
			<select name="vat_period_key" id="vat_period_key">
			<option value="">Vælg</option>
			<?php foreach ($allowed_periods AS $key=>$value): ?>
				<option value="<?php echo $key; ?>"<?php if ($key == $year->getSetting('vat_period')) echo ' selected="selected"'; ?>><?php echo safeToHtml($value['name']); ?></option>
			<?php endforeach; ?>
			</select>
			<input type="submit" value="Opret perioder" name="create_periods" />
		</fieldset>
	</form>
<?php else: ?>
	<table>
	<caption>Momsperioder i perioden <?php echo safeToHtml($year->get('from_date_dk')); ?> til <?php echo safeToHtml($year->get('to_date_dk')); ?></caption>
	<thead>
		<tr>
			<th>Periode</th>
			<th>Første dato</th>
			<th>Sidste dato</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($periods AS $period): ?>
		<tr>
			<td><a href="vat_view.php?id=<?php echo intval($period['id']); ?>"><?php echo safeToHtml($period['label']); ?></a></td>
			<td><?php echo safeToHtml($period['date_start_dk']); ?></td>
			<td><?php echo safeToHtml($period['date_end_dk']); ?></td>
			<td class="options"><a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $period['id']; ?>">Slet</a></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

<?php endif; ?>

<?php
$page->end();
?>