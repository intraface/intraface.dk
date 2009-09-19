<h1>Moms <?php e($year->get('label')); ?></h1>

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
	<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
		<fieldset>
			<label for="vat_period_key">Hvor ofte skal du afregne moms</label>
			<select name="vat_period_key" id="vat_period_key">
			<option value="">Vælg</option>
			<?php foreach ($allowed_periods AS $key=>$value): ?>
				<option value="<?php e($key); ?>"<?php if ($key == $year->getSetting('vat_period')) echo ' selected="selected"'; ?>><?php e($value['name']); ?></option>
			<?php endforeach; ?>
			</select>
			<input type="submit" value="Opret perioder" name="create_periods" />
		</fieldset>
	</form>
<?php else: ?>
	<table>
	<caption>Momsperioder i perioden <?php e($year->get('from_date_dk')); ?> til <?php e($year->get('to_date_dk')); ?></caption>
	<thead>
		<tr>
			<th>Periode</th>
			<th>Første dato</th>
			<th>Sidste dato</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($periods as $period): ?>
		<tr>
			<td><a href="vat_view.php?id=<?php e($period['id']); ?>"><?php e($period['label']); ?></a></td>
			<td><?php e($period['date_start_dk']); ?></td>
			<td><?php e($period['date_end_dk']); ?></td>
			<td class="options"><a class="delete" href="<?php e($_SERVER['PHP_SELF']); ?>?delete=<?php e($period['id']); ?>">Slet</a></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

<?php endif; ?>
