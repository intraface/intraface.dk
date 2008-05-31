<?php
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);

if ($year->get('id') > 0) {
	header('Location: daybook.php');
	exit;
}

$year = new Year($kernel);
$years = $year->getList();

$page = new Intraface_Page($kernel);
$page->start('Regnskab');
?>

<h1>Regnskab</h1>

<div class="message">
	<p><strong>Regnskab</strong>. I dette modul kan du lave dit virksomhedsregnskab.</p>
</div>

<?php if (count($years) == 0): ?>
	<p>Du skal <a href="year_edit.php">oprette et regnskab</a> for at komme i gang med at bruge regnskabsmodulet.</p>
<?php else: ?>
	<p><a href="years.php">Vælg et regnskab</a> du vil se eller ændre i.</p>
<?php endif; ?>

<?php
$page->end();
?>
