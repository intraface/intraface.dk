
<h1>Årsafslutning</h1>

<?php /*if (!$year->get('locked') == 1): ?>
	<p class="warning">�ret er lukket for bogf�ring. Du kan l�se det op under <a href="year_edit.php<?php e($context->getYear()->get('id')); ?>">�ret</a>.</p>
<?php */ if (!$context->getYear()->isSettingsSet()): ?>
	<p class="error">Kontoplanen er ikke delt op i resultatopgørelse og balance, eller der er ikke valgt en kapitalkonto. <a href="<?php e(url('../../../settings')); ?>">Gå til indstillingerne</a>.</p>
<?php elseif (count($context->getPost()->getList('draft')) > 0): ?>
	<p class="warning">Der er stadig poster i kassekladden. De skal bogføres, før du kan afslutte året. <a href="<?php e(url('../../../daybook')); ?>">Gå til kassekladden</a>.</p>
<?php elseif ($context->getYear()->get('vat') == 1 AND count($context->getVatPeriod()->getList()) == 0): ?>
	<p class="warning">Du har ikke oprettet nogen momsperioder. <a href="<?php e(url('../vat')); ?>">Opret perioder</a>.</p>
<?php elseif (!$context->getYear()->isBalanced()): ?>
	<p class="error">Balancen for året er <?php e(amountToOutput($context->getYear()->get('year_saldo'))); ?>. I et dobbelt bogholderi skal saldoen altid være 0, for ellers er der ikke er bogført lige meget på debet og credit. Du kan først lave årsafslutning når regnskabet stemmer. <a href="<?php e(url('../../../daybook')); ?>">Gå til kassekladden</a>.</p>
<?php else: ?>


<form action="<?php e(url()); ?>" method="post">
	<input type="hidden" name="step" value="<?php e($context->getYearEnd()->get('step') + 1); ?>" />

<?php
switch($context->getYearEnd()->get('step') + 1):

	case 1:
		?>

	<div class="message">
		<p><strong>Årsafslutning</strong>. Her kan du følge en guide til at afslutte dit årsregnskab.</p>
	</div>
		<fieldset>
			<legend>Trin 1: Sikre sig at alle poster er bogført</legend>
			<p>Det første du skal gøre, er at kigge en ekstra gang på alle dine bilag.</p>
			<ul>
				<li>Er alle bilag bogført - fakturaer, indkøb, kreditnotaer og rykkere?</li>
				<li>Har du bogført alle afskrivninger?</li>
				<li>Har du afstemt banken og kassen?</li>
			</ul>


		<?php if (!$context->getYear()->isStated('invoice', $context->getYear()->get('from_date'), $context->getYear()->get('to_date'))): ?>
			<p class="warning">Alle fakturaer i perioden er ikke bogført.
			<a href="<?php e(url('../../../debtor/invoice/list', array('type' => 'invoice', 'status' => -1, 'not_stated' => 'true', 'from_date' => $context->getYear()->get('from_date_dk'), 'to_date' => $context->getYear()->get('to_date_dk')))); ?>">Gå til ikke bogførte fakturaer</a>.</p>
		<?php endif; ?>

		<?php if (!$context->getYear()->isStated('credit_note', $context->getYear()->get('from_date'), $context->getYear()->get('to_date'))): ?>
			<p class="warning">Alle kreditnotaer i perioden er ikke bogført.
			<a href="<?php e(url('../../../debtor/credit_note/list', array('type' => 'credit_note', 'status' => -1, 'not_stated' => 'true', 'from_date' => $context->getYear()->get('from_date_dk'), 'to_date' => $context->getYear()->get('to_date_dk')))); ?>">Gå til ikke bogførte kreditnotaer</a>.</p>
		<?php endif; ?>

		<?php
			//$context->getVatPeriod() = new VatPeriod($context->getYear());
			$vat_periods = $context->getVatPeriod()->getList();

			foreach ($vat_periods as $period) {
				$vat_period = new VatPeriod($context->getYear(), $period['id']);
				if (!$vat_period->get('voucher_id')) {
					echo '<p class="warning">Momsperiode ' . $vat_period->get('label') . ' er ikke bogført. <a href="'.url('../vat/' . $vat_period->get('id')).'">Fix it at the vat period</a>.</p>';
				} elseif (!$context->getVatPeriod()->compareAmounts()) {
					echo '<p class="warning">Momsperiode ' . $vat_period->get('label') . ' stemmer ikke. <a href="'.url('../vat/' . $vat_period->get('id')).'">Fix it at the vat period</a>.</p>';
				}
			}
		?>

			<input type="submit" value="<?php e(t('Next')); ?>" name="step_things_stated" />
		</fieldset>
		<?php
	break;
	case 2:
		// her skal resultatopg�relsen gemmes.
		?>
		<fieldset>
			<legend>Trin 2: Gem resultatopgørelsen</legend>
			<?php if (count($context->getYearEnd()->getStatedActions('operating_reset')) == 0): ?>
			<input type="submit" name="previous" value="Forrige" />
			<input type="submit" name="step_save_result" value="Gem resultatopgørelsen" class="confirm" />
			<?php else: ?>
				<p>Resultatopgørelsen er allerede gemt. Du kan føre posterne tilbage, hvis du vil gemme igen.</p>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_reverse_result_reset" value="Tilbagefør posterne" class="confirm" />
				<input type="submit" name="next" value="<?php e(t('Next')); ?>" class="confirm" />
			<?php endif; ?>
		</fieldset>


		<?php
	break;
	case 3:
		// her skal resultatopg�relsen overf�res til statuskonti - og �rets resultat
		?>

		<?php if (count($context->getYearEnd()->getStatement('operating')) == 0): ?>
			<fieldset>
			<legend>Trin 3: Poster overføres til resultatopgørelseskontoen</legend>
			<p class="warning">Du er endnu ikke helt klar til dette trin, for resultatopgørelsen er ikke gemt.</p>
			<input type="submit" value="Forrige" name="previous" />
			</fieldset>
		<?php else: ?>
        <?php
        $status_accounts = $context->getAccount()->getList('status');
        ?>

		<fieldset>
			<legend>Trin 3: Poster overføres til resultatopgørelseskontoen</legend>
			<p>Her kan du automatisk overføre alle poster fra driftskonti til resultatopgørelsen. Derved nulstilles alle driftskonti. Lad være at trykke på knappen, hvis du ikke er helt sikker på, hvad du gør.</p>
			<div class="formrow">
			<label for="result_account">Poster overføres til</label>
			<select id="result_account" name="result_account_id">
				<option value=""><?php e(t('Choose')); ?></option>
				<?php foreach ($status_accounts as $a) { ?>
					<option value="<?php e($a['id']); ?>"<?php if ($context->getYear()->getSetting('result_account_id')==$a['id']) { echo ' selected="selected"'; } ?>><?php e($a['number']); ?> <?php e($a['name']); ?></option>
				<?php } ?>
			</select>
			</div>

			<div>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_result" value="Overfør poster" class="confirm" />
			</div>
		</fieldset>

	<table>
		<caption>Driftskonti</caption>
		<thead>
		<tr>
			<th>Nummer</th>
			<th>Navn</th>
			<th>Debet</th>
			<th>Kredit</th>
			<th>Saldo</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($context->getAccount()->getList('drift', true) AS $a): ?>
			<tr>
				<td><a href="<?php e(url('../../../../account/' . $a['id'])); ?>"><?php e($a['number']); ?></a></td>
				<td><?php e($a['name']); ?></td>
				<td><?php e(amountToOutput($a['debet'])); ?></td>
				<td><?php e(amountToOutput($a['credit'])); ?></td>
				<td><?php e(amountToOutput($a['saldo'])); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

		<?php
		endif;
	break;
	case 4:
		// her skal statusopg�relsen gemmes.
		?>
		<fieldset>
			<legend>Trin 4: Gem statusopgørelsen</legend>
			<input type="submit" name="previous" value="Forrige" />
			<input type="submit" name="step_save_balance" value="Gem balancen" class="confirm" />
		</fieldset>

		<?php
	break;

	case 5:
		// her skal man s� kunne afl�se resultatopg�relsen og balancen p� samme side
		$result_statements = $context->getYearEnd()->getStatement('operating');
		$balance_statements = $context->getYearEnd()->getStatement('balance');

		if (count($result_statements) == 0 OR count($balance_statements) == 0):
			?>
			<fieldset>
			<legend>Trin 5: Årsregnskabet</legend>
			<p class="warning">Du er ikke helt klar til dette trin endnu, for årsregnskabet er endnu ikke gemt.</p>
			<input name="previous" type="submit" value="Forrige" />
			</fieldset>

		<?php
		else:
		?>
		<fieldset>
			<legend>Trin 5: Årsregnskabet</legend>
			<p>Årsregnskabet er færdig. Du kan se det nedenunder - og du kan skrive det ud som et excel-ark. God fornøjelse.</p>
			<input name="previous" type="submit" value="Forrige" />
			<input name="next" type="submit" value="<?php e(t('Next')); ?>" />
		</fieldset>

		<ul class="options">
			<li><a class="excel" href="<?php e(url(null) . '.xls'); ?>">Excel</a></li>
		</ul>
		<table>
			<caption>Resultatopgørelse</caption>
			<thead>
				<tr>
					<th>Kontonummer</th>
					<th>Konto</th>
					<th>Beløb</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($result_statements AS $statement): ?>
			<tr<?php if ($statement['type'] == 'headline') { echo ' class="headline"'; } elseif ($statement['type'] == 'sum') { echo ' class="sum"';} ?>>

				<td><?php e($statement['number']); ?></td>
				<td><?php e($statement['name']); ?></td>
				<td><?php if ($statement['type'] != 'headline') e(amountToOutput(abs($statement['saldo']))); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>


		<table>
			<caption>Status</caption>
			<thead>
				<tr>
					<th>Kontonummer</th>
					<th>Konto</th>
					<th>Beløb</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($balance_statements AS $statement): ?>
			<tr<?php if ($statement['type'] == 'headline') { echo ' class="headline"'; } elseif ($statement['type'] == 'sum') { echo ' class="sum"';} ?>>

				<td><?php e($statement['number']); ?></td>
				<td><?php e($statement['name']); ?></td>
				<td><?php if ($statement['type'] != 'headline') e(amountToOutput(abs($statement['saldo']))); ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		endif;

		/*
		?>

			<table>

				<caption>Resultatopg�relse</caption>
				<thead>
				<tr>
					<th>Tekst</th>
					<th>Saldo</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan="2"><strong>Indt�gter</strong></td>
				</tr>
			<?php

				$db = new DB_Sql;
				$db->query("SELECT * FROM accounting_year_end_action INNER JOIN accounting_account ON accounting_year_end_action.debet_account_id = accounting_account.id WHERE use_key = 2"); // indkomst
				while ($db->nextRecord()) {
					?>
						<tr>
							<td><?php e($db->f('number') . ' ' . $db->f('name')); ?></td>
							<td><?php e(amountToOutput($db->f('amount'))); ?></td>
						</tr>
					<?php
				}

				?>
				<tr>
					<td colspan="2"><strong>Udgifter</strong></td>
				</tr>

				<?php
				$db->query("SELECT * FROM accounting_year_end_action INNER JOIN accounting_account ON accounting_year_end_action.debet_account_id = accounting_account.id WHERE use_key = 3"); // udgifter
				while ($db->nextRecord()) {
					?>
						<tr>
							<td><?php e($db->f('number') . ' ' . $db->f('name')); ?></td>
							<td><?php e(amountToOutput($db->f('amount'))); ?></td>
						</tr>
					<?php
				}
				// lave en sammenregning
			?>
				</tbody>
			</table>
		<?php
		*/
	break;

	case 6:
		// hvad skal vi g�re med �rets resultat?
		?>

			<fieldset>
				<legend>Trin 6: Årets resultat</legend>
			<?php if (count($context->getYearEnd()->getStatedActions('result_account_reset')) == 0): ?>
				<p>Årets resultat skal overføres til kapitalkontoen, så dine konti er klar til <?php e(t('Next')); ?> års regnskab.</p>
				<input type="submit" value="Forrige" name="previous" />
				<input type="submit" value="Gem" name="step_transfer_result" class="confirm" />
			<?php else: ?>
				<p>Årets resultat er allerede nulstillet. Du kan føre posterne tilbage, hvis du vil gemme igen.</p>
				<input type="submit" value="Forrige" name="previous" />
				<input type="submit" name="step_reverse_result_account_reset" value="Tilbagefør posterne" />

				<input type="submit" name="next" value="<?php e(t('Next')); ?>" />
			<?php endif; ?>

			</fieldset>
		<?php
	break;
	case 7:
		?>
			<fieldset>
			<legend>Trin 7: Lås året?</legend>
				<p>Efter en årsafslutning kan det være en god ide at låse året, så der ikke længere kan bogføres i det.</p>
				<div>
					<label><input type="radio" name="lock" value="1" <?php if ($context->getYear()->get('locked') == 1) echo ' checked="checked"'; ?>/> Lås</label>
					<label><input type="radio" name="lock" value="0"<?php if ($context->getYear()->get('locked') == 0) echo ' checked="checked"'; ?> /> Lås ikke</label>
				</div>
				<input type="submit" name="previous" value="Forrige" />
				<input type="submit" name="step_lock_year" value="<?php e(t('Next')); ?>" />
			</fieldset>
		<?php
	break;


	case 8:
		?>
			<fieldset>
				<legend>Trin 8: Fyraften</legend>
				<p>Det er godt arbejde. Nu har du fortjent en pause. Håber ikke det var for vanskeligt. Vi hører naturligvis altid gerne om dine oplevelser med programmet, så vi kan forbedre det mest muligt.</p>
				<p><a class="excel" href="<?php e(url(null) . '.xls'); ?>">Hent årsregnskabet i et regneark</a></p>
				<input type="submit" value="Forrige" name="previous" />
			</fieldset>
		<?php
	break;
	default:
		trigger_error('Ugyldigt trin');
	break;

endswitch;
?>
</form>
<!--
<ol>
	<li>Hvis man har v�ret gennem hele guiden og lavet det hele, skal der bare v�re et link til en rapport - hvor man har mulighed for at �ndre noget tekst i</li>
	<li>Tjekker om bogf�ringen stemmer</li>
	<li>Tjekker om momskonti er t�mte</li>

	<li>Vi laver det som en KLIK-GUIDE med f�lgende sp�rgsm�l:
		<ul style="margin: 2em;">
			<li>Er alle poster fra i �r indtastet?</li>
			<li>Er momsregnskabet lavet og er posterne registreret rigtigt?</li>
		</ul>
	</li>
	<li>Overf�r poster til resultatkontoen. Der skal nok laves en tabel til det - s� kan det evt. ogs� fortrydes igen.</li>
	<li>Viser resultatkontoen.</li>
	<li>Der sp�rges om hvilken konto resultatet skal overf�res til - hvilket ofte vil v�re �rets resultat.</li>
	<li>Det er det �rsafslutningen skal kunne, men <?php e(t('Next')); ?> �r skal s� kunne starte med f�lgende:
		<ul>
			<li>Et nyt regnskab med samme kontoplan og indstillinger oprettes.</li>
			<li>Der sp�rges om statuskonti skal overf�res til nye regnskab som primosaldo.</li>
		</ul>
	</li>
</ol>
-->

<?php endif; ?>
