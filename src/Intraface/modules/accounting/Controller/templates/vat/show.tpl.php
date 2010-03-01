<?php
        	$vat_period = $context->getVatPeriod();
        	$vat_period->loadAmounts();
        	$account_vat_in = $vat_period->get('account_vat_in');
        	$account_vat_out = $vat_period->get('account_vat_out');
        	$account_vat_abroad = $vat_period->get('account_vat_abroad');
        	$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
        	$saldo_total = $vat_period->get('saldo_total');


?>
<h1>Moms <a href="<?php e(url('../../')); ?>"><?php e($context->getYear()->get('label')); ?></a></h1>

<ul class="options">
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (!$context->getYear()->vatAccountIsSet()): ?>
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="<?php e(url('../../settings')); ?>">indstillingerne</a>.</p>
<?php else: ?>

	<?php echo $context->getError()->view(); ?>

	<?php if ($context->getVatPeriod()->get('status') == 'stated'): ?>
		<p class="message">Denne momsopgivelse er bogført. <a href="<?php e(url('../../voucher/' . $context->getVatPeriod()->get('voucher_id'))); ?>">Se bilag</a></p>
	<?php endif; ?>

	<?php if (!$context->getVatPeriod()->compareAmounts() AND $context->getVatPeriod()->get('status_key') > 0): // beløb skal vøre gemt ?>
		<p class="warning">Det ser ud til, at du ikke har fået bogført alle momsbeløbene korrekt. Denne momsangivelse burde være 0, når den er bogført.</p>
	<?php endif; ?>

	<?php if (!$context->getYear()->isStated('invoice', $context->getVatPeriod()->get('date_start'), $context->getVatPeriod()->get('date_end'))): ?>
		<p class="warning">Alle fakturaer i perioden er ikke bogført. <a href="<?php e(url('/restricted/module/debtor/invoice/list/', array('type' => 'credit_note', 'status' => -1, 'not_stated' => 'true', 'from_date' => $context->getVatPeriod()->get('date_start_dk'), 'to_date' => $context->getVatPeriod()->get('date_end_dk')))); ?>">Gå til fakturaer</a>.</p>
	<?php endif; ?>

	<?php if (!$context->getYear()->isStated('credit_note', $context->getVatPeriod()->get('date_start'), $context->getVatPeriod()->get('date_end'))): ?>
		<p class="warning">Alle kreditnotaer i perioden er ikke bogført. <a href="<?php e(url('/restricted/module/debtor/credit_note/list/', array('type' => 'credit_note', 'status' => -1, 'not_stated' => 'true', 'from_date' => $context->getVatPeriod()->get('date_start_dk'), 'to_date' => $context->getVatPeriod()->get('date_end_dk')))); ?>">Gå til kreditnotaer</a>.</p>
	<?php endif; ?>

	<table id="accounting-vat">
	<caption>Momsopgørelse for perioden <?php e($context->getVatPeriod()->get('date_start_dk')); ?> til <?php e($context->getVatPeriod()->get('date_end_dk')); ?></caption>
	<thead>
		<tr>
			<th>Kontonummer</th>
			<th>Kontobeskrivelse</th>
			<th colspan="2">Beløb fra regnskabet</th>
		</tr>
	</thead>
	<tbody>
		<tr class="vat-sale">
			<td><a href="<?php e(url('../../../account/' . $account_vat_out->get('id'))); ?>"><?php e($account_vat_out->get('number')); ?></a></td>
			<td><?php e($account_vat_out->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_out->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="vat-sale">
			<td><a href="<?php e(url('../../../account/' . $account_vat_abroad->get('id'))); ?>"><?php e($account_vat_abroad->get('number')); ?></a></td>
			<td><?php e($account_vat_abroad->get('name')); ?></td>
			<td></td>
			<td class="amount debet"><?php e(amountToOutput($account_vat_abroad->get('saldo') * -1)); ?></td>
		</tr>
		<tr class="headline">
			<td colspan="6"><h3>Fradrag</h3></td>
		</tr>
		<tr class="vat-buy">
			<td><a href="<?php e(url('../../../account/' . $account_vat_in->get('id'))); ?>"><?php e($account_vat_in->get('number')); ?></a></td>
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

	<?php if ($context->getKernel()->user->hasSubaccess('accounting', 'vat_report')): ?>
		<?php if ($context->getVatPeriod()->get('date_end') > date('Y-m-d')): ?>
			<p class="warning">Du er endnu ikke ude af perioden for momsafregningen, så det er en god ide at vente med at bogføre til du er sikker på alle beløbene.</p>
		<?php endif; ?>

		<?php if ($context->getVatPeriod()->get('status') != 'stated' OR !$context->getVatPeriod()->compareAmounts()): ?>
			<form action="<?php e(url()); ?>" method="post">

				<input type="hidden" name="id" value="<?php e($context->getVatPeriod()->get('id')); ?>" />
			<fieldset>
				<legend>Bogfør momsen</legend>
				<p>Du kan overføre beløbene til kassekladden ved at trykke på knappen nedenunder. Du bør først trykke på knappen, når du har opgivet beløbene til Skat.</p>
				<div class="formrow">
					<label for="date">Dato</label> <input type="text" name="date" id="date" value="<?php e($context->getVatPeriod()->get('date_end_dk')); ?>" />
				</div>
				<div class="formrow">
		        <?php if ($context->getVatPeriod()->get('status') == 'stated'): ?>
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($context->getVatPeriod()->get('voucher_number')); ?>" /> Perioden er tidligere bogført på dette bilag
				</div>
				<?php else: ?>
				<div class="formrow">
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($context->getVoucher()->getMaxNumber() + 1); ?>" />
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
