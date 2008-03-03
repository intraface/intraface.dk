<?php
/**
 * Test create reminder and pay is not finished
 */
require('../../include_first.php');
$module = $kernel->module("debtor");

$translation = $kernel->getTranslation('debtor');

$mainInvoice = $kernel->useModule("invoice");
$mainInvoice->includeFile("Reminder.php");
$mainInvoice->includeFile("ReminderItem.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $reminder = new Reminder($kernel, intval($_POST["id"]));
    
    // mark as sent
    if(!empty($_POST["mark_as_sent"])) {
        $reminder->setStatus("sent");
        
        if ($kernel->user->hasModuleAccess('accounting') && $reminder->somethingToState()) {
            header('location: state_reminder.php?id=' . intval($reminder->get("id")));
            exit;
        }
    }
}
else {
    
    $reminder = new Reminder($kernel, intval($_GET["id"]));
    if(isset($_GET['return_redirect_id'])) {
        $return_redirect = Redirect::factory($kernel, 'return');
    
        if($return_redirect->get('identifier') == 'send_email') {
            if($return_redirect->getParameter('send_email_status') == 'sent') {
                $reminder->setStatus('sent');
                $return_redirect->delete();
                
                if ($kernel->user->hasModuleAccess('accounting') && $reminder->somethingToState()) {
                    header('location: state_reminder.php?id=' . intval($reminder->get("id")));
                    exit;
                }
                
            }
    
        }
    }   
}

$page = new Page($kernel);
$page->start("Rykker");
?>
<div id="colOne"> <!-- style="float: left; width: 45%;" -->
    <div class="box">
        <h1>Rykker #<?php print(safeToHtml($reminder->get("number"))); ?></h1>
        
        <ul class="options">
        	<?php if($reminder->get("locked") == false) {
        		?>
        			<li><a href="reminder_edit.php?id=<?php print(intval($reminder->get("id"))); ?>">Ret</a></li>
        		<?php
        	}
        	?>
        
        		<li><a class="pdf" href="reminder_pdf.php?id=<?php print(intval($reminder->get("id"))); ?>" target="_blank">Udskriv PDF</a></li>
        	<?php
        	if($reminder->get("send_as") == "email" AND $reminder->get('status_key') < 1) {
        		?>
        		<li><a href="reminder_email.php?id=<?php print(intval($reminder->get("id"))); ?>">Send E-mail</a></li>
        		<?php
        	}
        	?>
        	<li><a href="list.php?type=invoice&amp;use_stored=true">Tilbage til fakturaer</a></li>
        	<li><a href="reminders.php?id=<?php print(intval($reminder->get("id"))); ?>&amp;use_stored=true">Luk</a></li>
        </ul>
        
        <p><?php print(safeToHtml($reminder->get('description'))); ?></p>
    
    </div>
    
    <?php echo $reminder->error->view(); ?>
    
    <form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
        
        <input type="hidden" name="id" value="<?php echo $reminder->get('id'); ?>" />
        <?php if(($reminder->get("status") == "created" AND $reminder->get("send_as") != "email")):  ?>
            <input type="submit" value="Marker som sendt" name="mark_as_sent" />
        <?php endif; ?>
    
    </form>

	<table>
		<caption>Rykkerinformationer</caption>
		<tr>
			<th>Dato:</th>
			<td><?php print(safeToHtml($reminder->get("dk_this_date"))); ?></td>
		</tr>
		<tr>
			<th>Forfaldsdato:</th>
			<td><?php print(safeToHtml($reminder->get("dk_due_date"))); ?></td>
		</tr>
		<tr>
			<th>Betalingmetode</th>
			<td><?php print(safeToHtml($reminder->get("payment_method"))); ?></td>
		</tr>
		<?php if($reminder->get("payment_method_key") == 3): ?>
			<tr>
				<th>Girolinje</th>
				<td>+71&lt;<?php echo str_repeat("0", 15 - strlen($reminder->get("girocode"))).safeToHtml($reminder->get("girocode")); ?> +<?php print(safeToHtml($kernel->setting->get("intranet", "giro_account_number"))); ?>&lt;</td>
			</tr>
		<?php endif; ?>
		<?php if($reminder->get("status") == "cancelled"): ?>
			<tr>
				<th>Afskrevet dato</th>
				<td><?php print(safeToHtml($reminder->get("dk_date_cancelled"))); ?></td>
			</tr>
		<?php endif; ?>
		<?php if($reminder->get("status") == "executed"): ?>
			<tr>
				<th>Færdigbehandlet dato:</th>
				<td><?php print(safeToHtml($reminder->get("dk_date_executed"))); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<th>Send som:</th>
			<td><?php print(safeToHtml($reminder->get("send_as"))); ?></td>
        </tr>

        <?php if ($kernel->setting->get('intranet', 'debtor.sender') == 'user' || $kernel->setting->get('intranet', 'debtor.sender') == 'defined'): ?>
    		<tr>
    			<th>Vores ref.</th>
				<td>
					<?php
					switch($kernel->setting->get('intranet', 'debtor.sender')) {
						case 'user':
							echo $kernel->user->getAddress()->get('name'). ' &lt;'.$kernel->user->getAddress()->get('email').'&gt;';
							break;
						case 'defined':
							echo $kernel->setting->get('intranet', 'debtor.sender.name').' &lt;'.$kernel->setting->get('intranet', 'debtor.sender.email').'&gt;';
							break;
					}
					
					if($kernel->user->hasModuleAccess('administration')) {
						$debtor_module = $kernel->useModule('debtor');
						echo ' <a href="'.$debtor_module->getPath().'setting.php" class="edit">'.safeToHtml($translation->get('change')).'</a></p>';	
					} 
					?>
				</td>
    		</tr>
    	<?php endif; ?>
		<tr>
			<th>Status:</th>
			<td><?php print(safeToHtml($translation->get($reminder->get("status")))); ?></td>
		</tr>
        <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
            <tr>
                <th>Bogført</th>
                <td>
                    <?php
                    if (!$reminder->somethingToState()) {
                        echo 'Der er intet at bogføre.';
                    }
                    elseif ($reminder->isStated()) {
                        $module_accounting = $kernel->useModule('accounting');
                        echo safeToHtml($reminder->get('dk_date_stated')) . ' <a href="'.$module_accounting->getPath().'voucher.php?id='.$reminder->get('voucher_id').'">Se bilag</a>';
                    }
                    else {
                        echo 'Ikke bogført';
                        if($reminder->get('status') == 'sent' || $reminder->get('status') == 'executed') {
                            echo ' <a href="state_reminder.php?id=' . intval($reminder->get("id")) . '">'.safeToHtml($translation->get('state reminder')).'</a>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php endif; ?>
	</table>

    <fieldset>
    	<legend>Tekst</legend>
    	<p><?php print(nl2br(safeToHtml($reminder->get("text")))); ?></p>
    </fieldset>
</div>

<div id="colTwo">

    <div class="box">
    	<table>
    		<caption>Kontaktoplysninger</caption>
    
    		<tr>
    			<th>Nummer</th>
    			<?php
    			$contact_module = $kernel->getModule('contact');
    			?>
    			<td><?php print(safeToHtml($reminder->contact->get("number"))); ?> <a href="<?php print($contact_module->getPath()); ?>contact_edit.php?id=<?php echo intval($reminder->contact->get('id')); ?>" class="edit">Ret</a></td>
    		</tr>
    		<tr>
    			<th>Kontakt</th>
    			<td><a href="<?php echo $contact_module->getPath().'contact.php?id='.$reminder->contact->get('id'); ?>"><?php print(safeToHtml($reminder->contact->address->get("name"))); ?></a></td>
    		</tr>
    		<tr>
    			<th>Adresse</th>
    			<td class="adr">
                    <div class="adr">
                        <div class="street-address"><?php print(nl2br(safeToHtml($reminder->contact->address->get("address")))); ?></div>
                        <span class="postal-code"><?php print safeToHtml($reminder->contact->address->get('postcode')); ?></span>  <span class="location"><?php echo safeToHtml($reminder->contact->address->get('city')); ?></span>
                        <div class="country"><?php echo safeToHtml($reminder->contact->address->get('country')); ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>E-mail</th>
                    <td><?php print(safeToHtml($reminder->contact->address->get("email"))); ?></td>
                </tr>
                <?php if($reminder->contact->address->get("cvr") != '' && $reminder->contact->address->get("cvr") != 0): ?>
                    <tr>
                        <th>CVR</th>
                        <td><?php echo safeToHtml($reminder->contact->address->get("cvr")); ?></td>
                    </tr>
                <?php endif; ?>
    		<?php if(isset($reminder->contact_person) && strtolower(get_class($reminder->contact_person)) == "contactperson"): ?>
    			<tr>
    				<th>Att.</th>
    				<td><?php echo safeToHtml($reminder->contact_person->get("name")); ?></td>
    			</tr>
    		<?php endif; ?>
    	</table>
    
    </div>

    <?php if($reminder->get("status") == "sent"): ?>
    	<div class="box">
            <h2>Registrér betaling</h2>
    		<div style="border: 2px solid red; padding: 5px; margin: 10px;">
    			<strong>Vigtigt</strong>: Registering af betaling her vedrører indtil videre KUN rukkergebyret på DENNE rykker. Dvs. du skal registere betalingen for fakturaer og tidligere rykkere på de respektive fakturaer og rykkere!
    		</div>
    		
            <form method="post" action="register_payment.php">
                <?php
                /**
                 * @TODO: hack as long as the payment types are not the same as on the reminder
                 */
                if($reminder->get('payment_method') == 2 || $reminder->get('payment_method') == 3) {
                    $payment_method = 1; // giro
                }
                elseif($reminder->get('round_off')) {
                    $payment_method = 3; // cash
                }
                else {
                    $payment_method = 0; // bank_transfer
                }
                
                $payment = new Payment($reminder);
                $types = $payment->getTypes();
                ?>
                <input type="hidden" value="<?php echo $reminder->get('id'); ?>" name="id" />
                <input type="hidden" value="reminder" name="for" />
                <input type="hidden" name="amount" value="<?php print(number_format($reminder->get("arrears"), 2, ",", ".")); ?>" />
                <input type="hidden" name="type" value="<?php e($payment_method); ?>" />
                
                <div>
                    <?php e(t('register')); ?> DKK <strong><?php e(number_format($reminder->get("arrears"), 2, ",", ".")); ?></strong> <?php e(t('paid by')); ?> <strong><?php e(t($types[$payment_method])); ?></strong>:
                </div>
                
                <div class="formrow">
                    <label for="payment_date" class="tight">Dato</label>
                    <input type="text" name="payment_date" id="payment_date" value="<?php print(safeToHtml(date("d-m-Y"))); ?>" size="8" />
                </div>

                <div style="clear: both;">
                    <input class="confirm" type="submit" name="payment" value="Registrér" title="Dette vil registrere betalingen" />
                    <?php e(t('or')); ?> 
                    <a href="register_payment.php?for=reminder&amp;id=<?php e($reminder->get('id')); ?>"><?php e(t('give me more choices')); ?></a>.
                </div>
            </form>
            <p><a href="register_depreciation.php?for=reminder&amp;id=<?php e($reminder->get('id')); ?>"><?php e(t('I am not going to recieve the full payment...')); ?></a></p>
        </div>
    <?php endif; ?>

</div> <!-- colTwo -->

<div style="clear:both;"></div>

<?php if($reminder->get('status') == 'sent' || $reminder->get('status') == 'executed'): ?>
	<?php
	$payments = $reminder->getDebtorAccount()->getList();
	$payment_total = 0;
	if(count($payments) > 0) {
		?>
		<table style="clear:both;">
			<caption>Betaling (rykkergebyr)</caption>
			<thead>
				<tr>
					<th>Dato</th>
					<th>Type</th>
					<th>Beskrivelse</th>
					<th>Beløb</th>
                    <?php if($kernel->user->hasModuleAccess('accounting')): ?>
                         <th>Bogført</th>
                    <?php endif; ?>
				</tr>
			</thead>
  		<tbody>
			<?php
			for($i = 0, $max = count($payments); $i < $max; $i++) {
				$payment_total += $payments[$i]["amount"];
				?>
				<tr>
					<td><?php print(safeToHtml($payments[$i]["dk_date"])); ?></td>
					<td><?php print(safeToHtml($payments[$i]["type"])); ?></td>
					<td>
						<?php
						if($payments[$i]["type"] == "credit_note") {
							?>
							<a href="view.php?id=<?php print(intval($payments[$i]["id"])); ?>"><?php print(safeToHtml($payments[$i]["description"])); ?></a>
							<?php
						}
						else {
							print(safeToHtml($payments[$i]["description"]));
						}
						?>
					</td>
					<td><?php print(number_format($payments[$i]["amount"], 2, ",", ".")); ?></td>
				    <?php if($kernel->user->hasModuleAccess('accounting')): ?>
                        <td>
                            <?php if($payments[$i]['is_stated']): ?>
                                <?php $module_accounting = $kernel->useModule('accounting'); ?>
                                <a href="<?php e($module_accounting->getPath().'voucher.php?id='.$payments[$i]['voucher_id']); ?>"><?php e($translation->get('voucher')); ?></a>
                            <?php elseif($payments[$i]['type'] == 'depreciation'): ?>
                                <a href="state_depreciation.php?for=reminder&amp;id=<?php e($reminder->get('id')); ?>&amp;depreciation_id=<?php e($payments[$i]['id']) ?>"><?php e($translation->get('state depreciation')); ?></a>
                            <?php else: ?>
                                <a href="state_payment.php?for=reminder&amp;id=<?php e($reminder->get('id')); ?>&amp;payment_id=<?php e($payments[$i]['id']) ?>"><?php e($translation->get('state payment')); ?></a>
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
				<td>Betalt</td>
				<td><?php print(number_format($payment_total, 2, ",", ".")); ?></td>
                <td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>Manglende betaling</td>
				<td><?php print(number_format($reminder->get("total") - $payment_total, 2, ",", ".")); ?></td>
			    <td>&nbsp;</td>
            </tr>
			</tbody>
		</table>
		<?php
	}
	?>
<?php endif; ?>



	<table class="stribe">
		<caption>Indhold</caption>
		<thead>
			<tr>
				<th>Nr.</th>
				<th>Beskrivelse</th>
				<th>Forfaldsdato</th>
				<th>Beløb</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$reminder->loadItem();
			$items = $reminder->item->getList("invoice");
			$total = 0;

			if(count($items) > 0) {
				?>
				<tr>
					<td colspan="4"><b>Fakturaer med restance:</b></td>
				</tr>
				<?php
			}

			for($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["arrears"];
				?>
				<tr>
					<td class="number"><?php print(safeToHtml($items[$i]["number"])); ?></td>
					<td><a href="<?php echo $module->getPath().'view.php?id='.intval($items[$i]['invoice_id']); ?>"><?php print(safeToHtml($items[$i]["description"])); ?></a></td>
					<td class="date"><?php print(safeToHtml($items[$i]["dk_due_date"])); ?></td>
					<td class="amount"><?php print(number_format($items[$i]["arrears"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			$items = $reminder->item->getList("reminder");
			if(count($items) > 0) {
				?>
				<tr>
  				<td colspan="4"><b>Tidligere rykkere:</b></td>
				</tr>
				<?php
			}



			for($i = 0, $max = 0; $i < count($items); $i++) {
				$total += $items[$i]["reminder_fee"];
				?>
				<tr>
					<td class="number"><?php print(safeToHtml($items[$i]["number"])); ?></td>
					<td><a href="reminder.php?id=<?php print(intval($items[$i]["reminder_id"])); ?>"><?php print(safeToHtml($items[$i]["description"])); ?></a></td>
					<td class="date"><?php print(safeToHtml($items[$i]["dk_due_date"])); ?></td>
					<td class="amount"><?php print(number_format($items[$i]["reminder_fee"], 2, ",",".")); ?></td>
				</tr>
				<?php
			}

			if($reminder->get("reminder_fee") != 0) {
				$total += $reminder->get("reminder_fee");
				?>
				<tr>
					<td colspan="2"><b>Rykkergebyr</b></td>
					<td class="date">&nbsp;</td>
					<td class="amount"><?php print(number_format($reminder->get("reminder_fee"), 2, ",",".")); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><strong>Total:</strong></td>
				<td class="amount"><strong><?php print(number_format($total, 2, ",",".")); ?></strong></td>
			</tr>
		</tfoot>
	</table>



<?php
$page->end();
?>