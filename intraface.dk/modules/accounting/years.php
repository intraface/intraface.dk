<?php
require '../../include_first.php';

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

// her sætter vi et år
if (!empty($_POST['id']) AND is_numeric($_POST['id'])) {
	$year = new Year($kernel, $_POST['id']);
	if (!$year->setYear()) {
		trigger_error('Kunne ikke sætte året', E_USER_ERROR);
	}

	header('Location: daybook.php');
	exit;

}

$year = new Year($kernel);
$years = $year->getList();

$page = new Intraface_Page($kernel);

$page->start('Vælg regnskab');
?>

<h1>Regnskabsår</h1>

<div class="message">
	<p><strong>Regnskabsår</strong>. På denne side kan du enten oprette et nyt regnskab eller vælge hvilket regnskab, du vil begynde at indtaste poster i. Du vælger regnskabet på listen nedenunder.</p>
</div>

<ul class="options">
	<li><a class="new" href="year_edit.php">Opret regnskabsår</a></li>
</ul>

<?php if (empty($years)): ?>
	<p>Der er ikke oprettet nogen regnskabsår. Du kan oprette et ved at klikke på knappen ovenover.</p>
<?php else: ?>
	<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<table>
		<caption>Regnskabsår</caption>
		<thead>
			<tr>
				<th></th>
				<th>År</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($years AS $y): ?>
		<tr>
			<td><input type="radio" name="id" value="<?php e($y['id']); ?>" <?php if ($year->loadActiveYear() == $y['id']) { echo ' checked="checked"'; } ?>/></td>
			<td><a href="year.php?id=<?php e($y['id']); ?>"><?php e($y['label']); ?></a></td>
			<td class="options">
				<a class="edit" href="year_edit.php?id=<?php e($y['id']); ?>">Ret</a>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="submit" value="Vælg" />
	</form>
<?php endif; ?>

<?php
$page->end();
?>
