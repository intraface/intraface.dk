<h1>Moms <?php e($year->get('label')); ?></h1>

<ul class="options">
	<li><a href="vat_period.php">Luk</a></li>
</ul>

<?php if (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php else: ?>

	<?php echo $error->view(); ?>

	<?php if ($vat_period->get('status') == 'stated'): ?>
		<p class="message">Denne momsopgivelse er bogført. <a href="<?php e($module->getPath()); ?>voucher.php?id=<?php e($vat_period->get('voucher_id')); ?>">Se bilag</a></p>
	<?php endif; ?>

	<?php if (!$vat_period->compareAmounts() AND $vat_period->get('status_key') > 0): // beløb skal være gemt ?>
		<p class="warning">Det ser ud til, at du ikke har fået bogført alle momsbeløbene korrekt. Denne momsangivelse burde være 0, når den er bogført.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('invoice', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle fakturaer i perioden er ikke bogført. <a href="/modules/debtor/list.php?type=invoice&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($vat_period->get('date_start_dk')); ?>&amp;to_date=<?php e($vat_period->get('date_end_dk')); ?>">Gå til fakturaer</a>.</p>
	<?php endif; ?>

	<?php if (!$year->isStated('credit_note', $vat_period->get('date_start'), $vat_period->get('date_end'))): ?>
		<p class="warning">Alle kreditnotaer i perioden er ikke bogført. <a href="/modules/debtor/list.php?type=credit_note&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($vat_period->get('date_start_dk')); ?>&amp;to_date=<?php e($vat_period->get('date_end_dk')); ?>">Gå til kreditnotaer</a>.</p>
	<?php endif; ?>

	<table id="accounting-vat">
	<caption>Momsopgørelse for perioden <?php e($vat_period->get('date_start_dk')); ?> til <?php e($vat_period->get('date_end_dk')); ?></caption>
	<thead>
		<tr>
			<th>Kontonummer</th>
			<th>Kontobeskrivelse</th>
			<th colspan="2">Beløb fra regnskabet</th>
		</tr>
	</thead>
	<tbody>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php e($account_vat_out->get('id')); ?>"><?php e($account_vat_out->get('number')); ?></a></td>
			<td><?php e($account_vat_out->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_out->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="vat-sale">
			<td><a href="account.php?id=<?php e($account_vat_abroad->get('id')); ?>"><?php e($account_vat_abroad->get('number')); ?></a></td>
			<td><?php e($account_vat_abroad->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_abroad->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="headline">
			<td colspan="6"><h3>Fradrag</h3></td>
		</tr>
		<tr class="vat-buy">
			<td><a href="account.php?id=<?php e($account_vat_in->get('id')); ?>"><?php e($account_vat_in->get('number')); ?></a></td>
			<td><?php e($account_vat_in->get('name')); ?></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_in->get('saldo'))); ?></td>
			<td></td>
		</tr>
		<tr class="vat-amount">
			<th colspan="2">Afgiftsbeløb i alt</th>
			<td></td>
			<td class="amount debet"><?php echo amountToOutput($saldo_total, 0); ?></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik A. Værdien uden moms af varekøb i andre <acronym title="Europæiske Union">EU</acronym>-lande</td>
			<!--<td class="amount credit"><?php e($saldo_rubrik_a); ?></td>-->
			<td class="amount debet"><?php e($saldo_rubrik_a); ?></td>
			<td></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik B. Værdien af varesalg uden moms til andre <acronym title="Europæiske Union">EU</acronym>-lande (EU-leverancer). Udfyldes rubrik B, skal der indsendes en liste</td>
			<td class="amount debet">Ikke understøttet</td>
			<td></td>
		</tr>
		<tr class="vat-rubrik">
			<td colspan="2">Rubrik C. Værdien af varer og ydelser, som sælges momsfrit til udlandet efter lovens §14-21 og 34, bortset fra varesalg til andre EU-lande, jf. rubrik B.</td>
			<td class="amount debet">Ikke understøttet</td>
			<td></td>
		</tr>
	</tbody>
	</table>

	<?php if ($kernel->user->hasSubaccess('accounting', 'vat_report')): ?>
		<?php if ($vat_period->get('date_end') > date('Y-m-d')): ?>
			<p class="warning">Du er endnu ikke ude af perioden for momsafregningen, så det er en god ide at vente med at bogføre til du er sikker på alle beløbene.</p>
		<?php endif; ?>

		<?php if ($vat_period->get('status') != 'stated' OR !$vat_period->compareAmounts()): ?>
			<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

				<input type="hidden" name="id" value="<?php e($vat_period->get('id')); ?>" />
			<fieldset>
				<legend>Bogfør momsen</legend>
				<p>Du kan overføre beløbene til kassekladden ved at trykke på knappen nedenunder. Du bør først trykke på knappen, når du har opgivet beløbene hos Skat.</p>
				<div class="formrow">
					<label for="date">Dato</label> <input type="text" name="date" id="date" value="<?php e($vat_period->get('date_end_dk')); ?>" />
				</div>
				<?php if ($vat_period->get('status') == 'stated'): ?>
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($vat_period->get('voucher_number')); ?>" /> Perioden er tidligere bogført på dette bilag
				</div>

				<?php else: ?>
				<div class="formrow">
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
				</div>
				<?php endif; ?>
				<div style="clear:both;">
					<input type="submit" name="state" value="Bogfør moms til momsafregning" />
				</div>
			</fieldset>
			</form>
		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>
