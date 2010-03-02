<?php
$reminder = $context->getReminder();
?>

<div id="colOne"> <!-- style="float: left; width: 45%;" -->
    <div class="box">
        <h1><?php e(t('Reminder')); ?> #<?php e($reminder->get("number")); ?></h1>

        <ul class="options">
        	<?php if ($reminder->get("locked") == false) {
        		?>
        			<li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
        		<?php
        	}
        	?>

        		<li><a class="pdf" href="<?php e(url(null . '.pdf')); ?>" target="_blank"><?php e(t('Print pdf')); ?></a></li>
        	<?php
        	if ($reminder->get("send_as") == "email" AND $reminder->get('status_key') < 1) {
        		?>
        		<li><a href="<?php e(url(null, array('email'))); ?>"><?php e(t('Send email')); ?></a></li>
        		<?php
        	}
        	?>
        	<li><a href="<?php e(url('../../invoice/list', array('use_stored' => true))); ?>"><?php e(t('Back to invoices')); ?></a></li>
        	<li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
        </ul>

        <p><?php e($reminder->get('description')); ?></p>

    </div>

    <?php echo $reminder->error->view(); ?>

    <form method="post" action="<?php e(url(null)); ?>">

        <input type="hidden" name="id" value="<?php e($reminder->get('id')); ?>" />
        <?php if (($reminder->get("status") == "created" AND $reminder->get("send_as") != "email")):  ?>
            <input type="submit" value="Marker som sendt" name="mark_as_sent" />
        <?php endif; ?>

    </form>

	<table>
		<caption><?php e(t('Reminder information')); ?></caption>
		<tr>
			<th><?php e(t('Date')); ?></th>
			<td><?php e($reminder->get("dk_this_date")); ?></td>
		</tr>
		<tr>
			<th><?php e(t('Due date')); ?></th>
			<td><?php e($reminder->get("dk_due_date")); ?></td>
		</tr>
		<tr>
			<th><?php e(t('Payment method')); ?></th>
			<td><?php e($reminder->get("payment_method")); ?></td>
		</tr>
		<?php if ($reminder->get("payment_method_key") == 3): ?>
			<tr>
				<th>Girolinje</th>
				<td>+71&lt;<?php echo str_repeat("0", 15 - strlen($reminder->get("girocode"))); ?><?php e($reminder->get("girocode")); ?> +<?php e($context->getKernel()->setting->get("intranet", "giro_account_number")); ?>&lt;</td>
			</tr>
		<?php endif; ?>
		<?php if ($reminder->get("status") == "cancelled"): ?>
			<tr>
				<th><?php e(t('Depreciation date')); ?></th>
				<td><?php e($reminder->get("dk_date_cancelled")); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ($reminder->get("status") == "executed"): ?>
			<tr>
				<th><?php e(t('Date closed')); ?></th>
				<td><?php e($reminder->get("dk_date_executed")); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<th><?php e(t('Send as')); ?></th>
			<td><?php e($reminder->get("send_as")); ?></td>
        </tr>

        <?php /*if ($context->getKernel()->setting->get('intranet', 'debtor.sender') == 'user' || $context->getKernel()->setting->get('intranet', 'debtor.sender') == 'defined'): ?>
    		<tr>
    			<th><?php e(t('Our contact')); ?></th>
				<td>
					<?php
					switch($context->getKernel()->setting->get('intranet', 'debtor.sender')) {
						case 'user':
							e($context->getKernel()->user->getAddress()->get('name'). ' <'.$context->getKernel()->user->getAddress()->get('email').'>');
							break;
						case 'defined':
							e($context->getKernel()->setting->get('intranet', 'debtor.sender.name').' <'.$context->getKernel()->setting->get('intranet', 'debtor.sender.email').'>');
							break;
					}

					if ($context->getKernel()->user->hasModuleAccess('administration')) {
						$debtor_module = $context->getKernel()->useModule('debtor');
                        ?>
						 <a href="<?php e($debtor_module->getPath()); ?>setting.php" class="edit"><?php e(t('Change')); ?></a>
                        <?php
					}
					?>
				</td>
    		</tr>
    	<?php endif;*/ ?>
		<tr>
			<th><?php e(t('Status')); ?></th>
			<td><?php e(t($reminder->get("status"))); ?></td>
		</tr>
        <?php if ($context->getKernel()->user->hasModuleAccess('accounting')): ?>
            <tr>
                <th><?php e(t('Stated')); ?></th>
                <td>
                    <?php
                    if (!$reminder->somethingToState()) {
                        e(t('Nothing to state'));
                    } elseif ($reminder->isStated()) {
                        $module_accounting = $context->getKernel()->useModule('accounting');
                        e($reminder->get('dk_date_stated')); ?>
                        <a href="<?php  e(url('../../../accounting/search', array('voucher_id' => $reminder->get('voucher_id')))); ?>"><?php e(t('See voucher')); ?></a>
                    	<?php
                    } else {
                        e(t('Not stated'));
                        if ($reminder->get('status') == 'sent' || $reminder->get('status') == 'executed') { ?>
                            <a href="<?php e(url('state')); ?>"><?php e(t('state reminder')); ?></a>
                        <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php endif; ?>
	</table>

    <fieldset>
    	<legend><?php e(t('Text')); ?></legend>
    	<p><?php autohtml($reminder->get("text")); ?></p>
    </fieldset>
</div>

<div id="colTwo">

    <div class="box">
    	<table>
    		<caption><?php e(t('Contact information')); ?></caption>

    		<tr>
    			<th><?php e(t('Number')); ?></th>
    			<?php
    			$contact_module = $context->getKernel()->getModule('contact');
    			?>
    			<td><?php e($reminder->contact->get("number")); ?> <a href="<?php e(url('../../../contact/' . $reminder->contact->get('id'), array('edit'))); ?>" class="edit">Ret</a></td>
    		</tr>
    		<tr>
    			<th><?php e(t('Contact')); ?></th>
    			<td><a href="<?php e(url('../../../contact/'.$reminder->contact->get('id'))); ?>"><?php e($reminder->contact->address->get("name")); ?></a></td>
    		</tr>
    		<tr>
    			<th>Address</th>
    			<td class="adr">
                    <div class="adr">
                        <div class="street-address"><?php autohtml($reminder->contact->address->get("address")); ?></div>
                        <span class="postal-code"><?php e($reminder->contact->address->get('postcode')); ?></span>  <span class="location"><?php e($reminder->contact->address->get('city')); ?></span>
                        <div class="country"><?php e($reminder->contact->address->get('country')); ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php e(t('Email')); ?></th>
                    <td><?php e($reminder->contact->address->get("email")); ?></td>
                </tr>
                <?php if ($reminder->contact->address->get("cvr") != '' && $reminder->contact->address->get("cvr") != 0): ?>
                    <tr>
                        <th><?php e(t('CVR')); ?></th>
                        <td><?php e($reminder->contact->address->get("cvr")); ?></td>
                    </tr>
                <?php endif; ?>
    		<?php if (isset($reminder->contact_person) && strtolower(get_class($reminder->contact_person)) == "contactperson"): ?>
    			<tr>
    				<th><?php e(t('Attention')); ?></th>
    				<td><?php e($reminder->contact_person->get("name")); ?></td>
    			</tr>
    		<?php endif; ?>
    	</table>

    </div>

    <?php if ($reminder->get("status") == "sent"): ?>
    	<div class="box">
            <h2><?php e(t('Register payment')); ?></h2>
    		<div style="border: 2px solid red; padding: 5px; margin: 10px;">
    			<strong>Vigtigt</strong>: Registering af betaling her vedr�rer indtil videre KUN rukkergebyret p� DENNE rykker. Dvs. du skal registere betalingen for fakturaer og tidligere rykkere p� de respektive fakturaer og rykkere!
    		</div>

            <form method="post" action="<?php e(url('payment')); ?>">
                <?php
                /**
                 * @TODO: hack as long as the payment types are not the same as on the reminder
                 */
                if ($reminder->get('payment_method') == 2 || $reminder->get('payment_method') == 3) {
                    $payment_method = 1; // giro
                } elseif ($reminder->get('round_off')) {
                    $payment_method = 3; // cash
                } else {
                    $payment_method = 0; // bank_transfer
                }

                $payment = new Payment($reminder);
                $types = $payment->getTypes();
                ?>
                <input type="hidden" value="<?php e($reminder->get('id')); ?>" name="id" />
                <input type="hidden" value="reminder" name="for" />
                <input type="hidden" name="amount" value="<?php e(number_format($reminder->get("arrears"), 2, ",", ".")); ?>" />
                <input type="hidden" name="type" value="<?php e($payment_method); ?>" />

                <div>
                    <?php e(t('register')); ?> DKK <strong><?php e(number_format($reminder->get("arrears"), 2, ",", ".")); ?></strong> <?php e(t('paid by')); ?> <strong><?php e(t($types[$payment_method])); ?></strong>:
                </div>

                <div class="formrow">
                    <label for="payment_date" class="tight"><?php e(t('Date')); ?></label>
                    <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" size="8" />
                </div>

                <div style="clear: both;">
                    <input class="confirm" type="submit" name="payment" value="Registr�r" title="Dette vil registrere betalingen" />
                    <a href="<?php e(url('payment')); ?>"><?php e(t('Give me more choices')); ?></a>.
                </div>
            </form>
            <p><a href="<?php e(url('depreciation')); ?>"><?php e(t('I am not going to recieve the full payment...')); ?></a></p>
        </div>
    <?php endif; ?>

</div> <!-- colTwo -->

<div style="clear:both;"></div>

<?php if ($reminder->get('status') == 'sent' || $reminder->get('status') == 'executed'): ?>
	<?php
	$payments = $reminder->getDebtorAccount()->getList();
	$payment_total = 0;
	if (count($payments) > 0) {
		?>
		<table style="clear:both;">
			<caption><?php e(t('Payment (reminder fee)')); ?></caption>
			<thead>
				<tr>
					<th><?php e(t('Date')); ?></th>
					<th><?php e(t('Type')); ?></th>
					<th><?php e(t('Description')); ?></th>
					<th><?php e(t('Amount')); ?></th>
                    <?php if ($context->getKernel()->user->hasModuleAccess('accounting')): ?>
                         <th><?php e(t('Stated')); ?></th>
                    <?php endif; ?>
				</tr>
			</thead>
  		<tbody>
			<?php
			for ($i = 0, $max = count($payments); $i < $max; $i++) {
				$payment_total += $payments[$i]["amount"];
				?>
				<tr>
					<td><?php e($payments[$i]["dk_date"]); ?></td>
					<td><?php e($payments[$i]["type"]); ?></td>
					<td>
						<?php
						if ($payments[$i]["type"] == "credit_note") {
							?>
							<a href="view.php?id=<?php e($payments[$i]["id"]); ?>"><?php e($payments[$i]["description"]); ?></a>
							<?php
						} else {
							e($payments[$i]["description"]);
						}
						?>
					</td>
					<td><?php e(number_format($payments[$i]["amount"], 2, ",", ".")); ?></td>
				    <?php if ($context->getKernel()->user->hasModuleAccess('accounting')): ?>
                        <td>
                            <?php if ($payments[$i]['is_stated']): ?>
                                <?php $module_accounting = $context->getKernel()->useModule('accounting'); ?>
                                <a href="<?php e(url('../../../accounting/search', array('voucher_id' => $payments[$i]['voucher_id']))); ?>"><?php e(t('voucher')); ?></a>
                            <?php elseif ($payments[$i]['type'] == 'depreciation'): ?>
                                <a href="<?php e(url('depreciation/' . $payments[$i]['id'] . '/state')); ?>"><?php e(t('state depreciation')); ?></a>
                            <?php else: ?>
                                <a href="<?php e(url('payment/' . $payments[$i]['id'] . '/state')); ?>"><?php e(t('state payment')); ?></a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
				<?php
			}

			?>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php e(t('Paid')); ?></td>
				<td><?php e(number_format($payment_total, 2, ",", ".")); ?></td>
                <td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php e(t('Missing payments')); ?></td>
				<td><?php e(number_format($reminder->get("total") - $payment_total, 2, ",", ".")); ?></td>
			    <td>&nbsp;</td>
            </tr>
			</tbody>
		</table>
		<?php
	}
	?>
<?php endif; ?>



	<table class="stribe">
		<caption><?php e(t('Content')); ?></caption>
		<thead>
			<tr>
				<th><?php e(t('No.')); ?></th>
				<th><?php e(t('Description')); ?></th>
				<th><?php e(t('Due date')); ?></th>
				<th><?php e(t('Amount')); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$items = $reminder->getItems("invoice");
			$total = 0;

			if (count($items) > 0) {
				?>
				<tr>
					<td colspan="4"><b><?php e(t('Invoices with missing payments:')); ?></b></td>
				</tr>
				<?php
			}

			for ($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["arrears"];
				?>
				<tr>
					<td class="number"><?php e($items[$i]["number"]); ?></td>
					<td><a href="<?php e(url('../../invoice/list/' . intval($items[$i]['invoice_id']))); ?>"><?php e($items[$i]["description"]); ?></a></td>
					<td class="date"><?php e($items[$i]["dk_due_date"]); ?></td>
					<td class="amount"><?php e(number_format($items[$i]["arrears"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			$items = $reminder->item->getList("reminder");
			if (count($items) > 0) {
				?>
				<tr>
  				<td colspan="4"><b><?php e(t('Earlier reminders')); ?></b></td>
				</tr>
				<?php
			}

			for ($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["reminder_fee"];
				?>
				<tr>
					<td class="number"><?php e($items[$i]["number"]); ?></td>
					<td><a href="reminder.php?id=<?php e($items[$i]["reminder_id"]); ?>"><?php e($items[$i]["description"]); ?></a></td>
					<td class="date"><?php e($items[$i]["dk_due_date"]); ?></td>
					<td class="amount"><?php e(number_format($items[$i]["reminder_fee"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			if ($reminder->get("reminder_fee") != 0) {
				$total += $reminder->get("reminder_fee");
				?>
				<tr>
					<td colspan="2"><b><?php e(t('Reminder fee')); ?></b></td>
					<td class="date">&nbsp;</td>
					<td class="amount"><?php e(number_format($reminder->get("reminder_fee"), 2, ",",".")); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><strong><?php e(t('Total')); ?></strong></td>
				<td class="amount"><strong><?php e(number_format($total, 2, ",",".")); ?></strong></td>
			</tr>
		</tfoot>
	</table>
