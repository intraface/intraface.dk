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
	<p class="message-dependent">Der er ikke angivet nogen momskonti. Du kan angive momskonti under <a href="setting.php">indstillingerne</a>.</p>
<?php else: ?>

	<?php echo $context->getError()->view(); ?>

	<?php if ($context->getVatPeriod()->get('status') == 'stated'): ?>
		<p class="message">Denne momsopgivelse er bogført. <a href="<?php e(url('../../voucher/' . $context->getVatPeriod()->get('voucher_id'))); ?>">Se bilag</a></p>
	<?php endif; ?>

	<?php if (!$context->getVatPeriod()->compareAmounts() AND $context->getVatPeriod()->get('status_key') > 0): // beløb skal vøre gemt ?>
		<p class="warning">Det ser ud til, at du ikke har fået bogført alle momsbeløbene korrekt. Denne momsangivelse burde vøre 0, nør den er bogført.</p>
	<?php endif; ?>

	<?php if (!$context->getYear()->isStated('invoice', $context->getVatPeriod()->get('date_start'), $context->getVatPeriod()->get('date_end'))): ?>
		<p class="warning">Alle fakturaer i perioden er ikke bogført. <a href="/modules/debtor/list.php?type=invoice&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($context->getVatPeriod()->get('date_start_dk')); ?>&amp;to_date=<?php e($context->getVatPeriod()->get('date_end_dk')); ?>">Gø til fakturaer</a>.</p>
	<?php endif; ?>

	<?php if (!$context->getYear()->isStated('credit_note', $context->getVatPeriod()->get('date_start'), $context->getVatPeriod()->get('date_end'))): ?>
		<p class="warning">Alle kreditnotaer i perioden er ikke bogført. <a href="/modules/debtor/list.php?type=credit_note&amp;status=-1&amp;not_stated=true&amp;from_date=<?php e($context->getVatPeriod()->get('date_start_dk')); ?>&amp;to_date=<?php e($context->getVatPeriod()->get('date_end_dk')); ?>">Gø til kreditnotaer</a>.</p>
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
			<td colspan="2">Rubrik A. Vørdien uden moms af varekøb i andre <acronym title="Europøiske Union">EU</acronym>-lande</td>
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
			<td colspan="2">Rubrik C. Vørdien af varer og ydelser, som sølges momsfrit til udlandet efter lovens ø14-21 og 34, bortset fra varesalg til andre EU-lande, jf. rubrik B.</td>
			<td class="amount debet">Ikke understøttet</td>
			<td></td>
		</tr>
	</tbody>
	</table>

	<?php if ($context->getKernel()->user->hasSubaccess('accounting', 'vat_report')): ?>
		<?php if ($context->getVatPeriod()->get('date_end') > date('Y-m-d')): ?>
			<p class="warning">Du er endnu ikke ude af perioden for momsafregningen, sø det er en god ide at vente med at bogføre til du er sikker pø alle beløbene.</p>
		<?php endif; ?>

		<?php if ($context->getVatPeriod()->get('status') != 'stated' OR !$context->getVatPeriod()->compareAmounts()): ?>
			<form action="<?php e(url()); ?>" method="post">

				<input type="hidden" name="id" value="<?php e($context->getVatPeriod()->get('id')); ?>" />
			<fieldset>
				<legend>Bogfør momsen</legend>
				<p>Du kan overføre beløbene til kassekladden ved at trykke pø knappen nedenunder. Du bør først trykke pø knappen, nør du har opgivet beløbene hos Skat.</p>
				<div class="formrow">
					<label for="date">Dato</label> <input type="text" name="date" id="date" value="<?php e($context->getVatPeriod()->get('date_end_dk')); ?>" />
				</div>
				<div class="formrow">
		        <?php if ($context->getVatPeriod()->get('status') == 'stated'): ?>
					<label for="voucher_number">Bilagsnummer</label> <input type="text" name="voucher_number" id="voucher_number" value="<?php e($context->getVatPeriod()->get('voucher_number')); ?>" /> Perioden er tidligere bogført pø dette bilag
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
