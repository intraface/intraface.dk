<div id="colOne">

<div class="box">
    <h1><?php e(t($context->getDebtor()->get("type"))); ?> #<?php e($context->getDebtor()->get("number")); ?></h1>

    <ul class="options">
        <?php if ($context->getDebtor()->get("locked") == false) : ?>
            <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
        <?php endif; ?>
        <li><a class="pdf" href="<?php e(url('.pdf')); ?>" target="_blank"><?php e(t('Pdf')); ?></a></li>
        <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
    </ul>

    <p><?php e($context->getDebtor()->get('description')); ?></p>
</div>

<?php echo $context->getDebtor()->error->view(); ?>
<?php
// onlinepayment error viewing, also with showing cancel onlinepayment button.
if (isset($context->onlinepayment)) {
    echo $context->onlinepayment->error->view();
    if (isset($context->onlinepayment_show_cancel_option) && $context->onlinepayment_show_cancel_option == true) {
        echo '<form method="post" action="'.url(null).'"><ul class="formerrors"><li>Ønsker du i stedet at <input type="submit" name="onlinepayment_cancel" value="Annullere" /><input type="hidden" name="id" value="'.$context->getDebtor()->get('id').'" /><input type="hidden" name="onlinepayment_id" value="'.$context->onlinepayment->id.'" /> registreringen af betalingen.</li></ul></form>';
    }
}
?>
<?php if ($context->getKernel()->intranet->get("pdf_header_file_id") == 0 && $context->getKernel()->user->hasModuleAccess('administration')) : ?>
    <div class="message-dependent">
        <p><a href="<?php e(url('../../../../administration/intranet')); ?>"><?php e(t('Upload a logo for your pdf\'s')); ?></a> </p>
    </div>
<?php endif; ?>

<?php echo $context->getMessageAboutEmail(); ?>

<?php if (isset($context->email_send_with_success) && $context->email_send_with_success) : ?>
    <div class="message-dependent"><p><?php e(t('Your email was sent').'.'); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php e(url()); ?>">
    <input type="hidden" name="id" value="<?php e($context->getDebtor()->get('id')); ?>" />
    <?php if ($context->getDebtor()->contact->get('preferred_invoice') == 2 and  $context->getDebtor()->get('status') == 'created' and $context->isValidSender()) : ?>
        <input type="submit" value="<?php e(t('Send on email')); ?>" name="send_email" title="<?php e(t('Are you sure?')); ?>" />
    <?php elseif ($context->getDebtor()->contact->get('preferred_invoice') == 2 and $context->getDebtor()->get('status') == 'sent' and $context->isValidSender()) : ?>
        <input type="submit" value="<?php e(t('Resend on email')); ?>" name="send_email" title="<?php e(t('Are you sure?')); ?>" />
    <?php elseif ($context->getDebtor()->get("type") == 'invoice' and $context->getDebtor()->contact->get('preferred_invoice') == 3 and $context->getDebtor()->contact->address->get('ean') and $context->getDebtor()->get('status') == 'created' and $context->isValidScanInContact()) : ?>
        <input type="submit" value="<?php e(t('Send electronic invoice')); ?>" name="send_electronic_invoice" title="<?php e(t('Are you sure you want to send the invoice to the Læs-ind-bureau?')); ?>" />
    <?php elseif ($context->getDebtor()->get("type") == 'invoice' and $context->getDebtor()->contact->get('preferred_invoice') == 3 and $context->getDebtor()->contact->address->get('ean') and $context->getDebtor()->get('status') == 'sent' and $context->isValidScanInContact()) : ?>
        <input type="submit" value="<?php e(t('Resend electronic invoice')); ?>" name="send_electronic_invoice" title="<?php e(t('Are you sure?')); ?>" />
    <?php endif; ?>
    <?php if ($context->getDebtor()->get("status") == "created") : // make sure we can always mark as sent	?>
        <input type="submit" value="<?php e(t('Mark as sent')); ?>" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="sent" />
    <?php endif; ?>
    <?php if (($context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") == "created") || ($context->getDebtor()->get("type") != "invoice" && $context->getDebtor()->get("locked") == false)) : ?>
        <input type="submit" value="<?php e(t('Delete')); ?>" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="delete" />
    <?php endif; ?>
    <?php if (($context->getDebtor()->get("type") == "quotation" || $context->getDebtor()->get("type") == "order") && ($context->getDebtor()->get('status') == "created" || $context->getDebtor()->get('status') == "sent")) : ?>
        <input type="submit" value="<?php e(t('Cancel')); ?>" name="cancel" class="confirm" title="<?php e(t('Are you sure?')); ?>" />
    <?php endif; ?>
    <?php if ($context->getDebtor()->get("type") == "quotation" && $context->getDebtor()->get('status') == "sent" && $context->getKernel()->user->hasModuleAccess('order')) : ?>
        <input type="submit" value="<?php e(t('Order this')); ?>" name="order" class="confirm" value="<?php e(t('Are you sure?')); ?>" />
    <?php endif; ?>
    <?php if ($context->getDebtor()->get("type") == "quotation" && $context->getDebtor()->get("status") == "sent" && $context->getKernel()->user->hasModuleAccess('invoice')) : ?>
        <input type="submit" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="invoice" value="<?php e(t('Invoice this')); ?>" />
    <?php endif; ?>
    <?php if ($context->getDebtor()->get("type") == "order" && $context->getDebtor()->get("where_to_id") == 0 && $context->getKernel()->user->hasModuleAccess('invoice')) : ?>
        <input type="submit" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="invoice" value="<?php e(t('Invoice this')); ?>" />
    <?php endif; ?>
    <?php if (1 == 2 and $context->getDebtor()->get("type") == "order" && $context->getDebtor()->get("where_to_id") == 0 && $context->getKernel()->user->hasModuleAccess('invoice')) : ?>
        <input type="submit" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="quickprocess_order" value="<?php e(t('Quick process')); ?>" />
    <?php endif; ?>
    <?php if ($context->getDebtor()->get("type") == "invoice" && ($context->getDebtor()->get("status") == "sent" or $context->getDebtor()->get("status") == 'executed')) : // Opret kreditnota fra faktura ?>
        <input type="submit" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="credit_note" value="<?php e(t('Make credit note from invoice')); ?>" />
    <?php endif; ?>
    <?php if (1 == 2 and $context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") != 'executed') : // Opret kreditnota fra faktura ?>
        <input type="submit" class="confirm" title="<?php e(t('Are you sure?')); ?>" name="quickprocess_invoice" value="<?php e(t('Quick process')); ?>" />
    <?php endif; ?>


</form>

<?php /* ?>
    <?php if (count($context->getDebtor()->contact->compare()) > 0 && $context->getDebtor()->get('locked') == false) {	?>
        <div style="border: 2px orange solid; padding: 1.5em; margin: 1em 0;">
        <h2 style="margin-top: 0; border-left: 10px solid green; padding-left: 0.5em; font-size: 1em; font-weight: strong;">Kunden eksisterer m�ske allerede i databasen?</h2>
        <p>Kunden ligner nogle af de andre kunder i kundekartoteket (baseret p� e-mail og postnummer). Du kan �ndre kunde p� ordren ved at v�lge en i listen nedenunder.</p>
        <table>
            <thead>
              <tr>
                <th>Navn</th>
                <th>Adresse</th>
                <th>Postby</th>
             <th>Telefon</th>
             <th>E-mail</th>
             <th></th>
            </tr>
          </thead>
          <tbody>
            <?php
                foreach ($context->getDebtor()->contact->compare() AS $value=>$key) {
                $contact = new Contact($context->getKernel(), $key);
                ?>
                <tr>
                    <td><?php e($contact->address->get('name')); ?></td>
                    <td><?php e($contact->address->get('address')); ?></td>
                    <td><?php e($contact->address->get('postcode')); ?> <?php e($contact->address->get('city')); ?></td>
                    <td><?php e($contact->address->get('phone')); ?></td>
                    <td><?php e($contact->address->get('email')); ?></td>
                    <td><a href="<?php e($_SERVER['PHP_SELF']); ?>?action=changecontact&amp;new_id=<?php e($contact->get('id')); ?>&amp;id=<?php e($context->getDebtor()->get('id')); ?>" onclick="return confirm('Er du sikker p� at du vil erstatte den nuv�rende kunde med den der er fundet i det eksisterende adressekartotek?');">[V�lg]</a></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        </div>
    <?php } ?>

<?php */ ?>

    <table>
        <caption><?php e(t($context->getDebtor()->get('type'))); ?> <?php e(t('information')); ?></caption>
        <tbody>
            <tr>
                <th><?php e(t('Date')); ?></th>
                <td><?php e($context->getDebtor()->get("dk_this_date")); ?></td>
            </tr>
            <?php if ($context->getDebtor()->get("type") != "credit_note") : ?>
            <tr>
                <th><?php e(t($context->getDebtor()->get('type').' due date')); ?></th>
                <td>
                    <?php e($context->getDebtor()->get("dk_due_date")); ?>
                    <?php if ($context->getDebtor()->get('type')=='invoice' && count($context->getDebtor()->anyDue($context->getDebtor()->contact->get('id'))) > 0 && $context->getDebtor()->get("status") != 'executed') {
                        echo '<a href="'.url('../../../reminders', array('create', 'contact_id' => intval($context->getDebtor()->contact->get('id')))).'">'.t('Create reminder').'</a>';
} ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if ($context->getKernel()->getSetting()->get('intranet', 'debtor.sender') == 'user' || $context->getKernel()->getSetting()->get('intranet', 'debtor.sender') == 'defined') : ?>
                <tr>
                    <th><?php e(t('Our contact')); ?></th>
                        <td>
                            <?php
                            switch ($context->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
                                case 'user':
                                    e($context->getKernel()->user->getAddress()->get('name'). ' <'.$context->getKernel()->user->getAddress()->get('email').'>');
                                    break;
                                case 'defined':
                                    e($context->getKernel()->getSetting()->get('intranet', 'debtor.sender.name').' <'.$context->getKernel()->getSetting()->get('intranet', 'debtor.sender.email').'>');
                                    break;
                            }

                            if ($context->getKernel()->user->hasModuleAccess('administration')) { ?>
                                <a href="<?php e(url('../../../settings')); ?>" class="edit"><?php e(t('Change')); ?></a>
                            <?php
                            }
                            ?>
                        </td>
                </tr>
            <?php endif; ?>
            <tr>
                <th><?php e(t('Status')); ?></th>
                <td>
                    <?php
                        e(t($context->getDebtor()->get("status")));

                    ?>
                </td>
            </tr>
            <?php if ($context->getDebtor()->get("type") == "invoice" || $context->getDebtor()->get("type") == "order") {   ?>
                <tr>
                    <th><?php e(t('Payment method')); ?></th>
                    <td><?php e($context->getDebtor()->get("translated_payment_method")); ?></td>
                </tr>
                <?php if ($context->getDebtor()->get("payment_method") == 3) { ?>
                    <tr>
                        <th>Girolinje</th>
                        <td>+71&lt;<?php echo str_repeat("0", 15 - strlen($context->getDebtor()->get("girocode"))).e($context->getDebtor()->get("girocode")); ?> +<?php e($context->getKernel()->getSetting()->get("intranet", "giro_account_number")); ?>&lt;</td>
                    </tr>
                <?php } ?>

                <?php if ($context->getDebtor()->get("status") == "executed") { ?>
                    <tr>
                        <th><?php e(t('Date executed')); ?></th>
                        <td><?php e($context->getDebtor()->get("dk_date_executed")); ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <th><?php e(t('Where from')); ?></th>
                <td>
                    <?php if (($context->getDebtor()->get('where_from') == 'quotation' || $context->getDebtor()->get('where_from') == 'order' || $context->getDebtor()->get('where_from') == 'invoice') && $context->getDebtor()->get("where_from_id") > 0) { ?>
                        <a href="<?php e(url('../' . $context->getDebtor()->get("where_from_id"))); ?>"><?php e(t($context->getDebtor()->get("where_from"))); ?></a>
                <?php } else { ?>
                        <?php e(t($context->getDebtor()->get('where_from'))); ?>
                    <?php } ?>
                </td>
            </tr>
            <?php if ($context->getDebtor()->get('where_to') and $context->getDebtor()->get('where_to_id')) : ?>
            <tr>
                <th><?php e(t('Where to')); ?></th>
                <td><a href="<?php e(url('../' . $context->getDebtor()->get('where_to_id'))); ?>"><?php e(t($context->getDebtor()->get('where_to'))); ?></a></td>
            </tr>
            <?php endif; ?>
            <?php if (($context->getDebtor()->get("type") == 'credit_note' || $context->getDebtor()->get("type") == 'invoice') and $context->getKernel()->user->hasModuleAccess('accounting')) : ?>
            <tr>
                <th><?php e(t('Stated')); ?></th>
                <td>
                    <?php
                    if ($context->getDebtor()->isStated()) {
                        $module_accounting = $context->getKernel()->useModule('accounting');
                        e($context->getDebtor()->get('dk_date_stated'));
                        echo ' <a href="'.url('../../../../accounting/search', array('voucher_id' => $context->getDebtor()->get('voucher_id'))).'">'.t('See voucher').'</a>';
                    } else {
                        e(t('Not stated'));
                        if ($context->getDebtor()->get('status') == 'sent' || $context->getDebtor()->get('status') == 'executed') { ?>
                                <a href="<?php e(url('state')); ?>"><?php e(t('State')); ?></a>
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($context->getDebtor()->get("message") != '') : ?>
        <fieldset>
            <legend><?php e(t('Text')); ?></legend>
            <p><?php autohtml($context->getDebtor()->get("message")); ?></p>
        </fieldset>
    <?php endif; ?>

    <?php if ($context->getDebtor()->get("internal_note") != '') : ?>
        <fieldset>
            <legend><?php e(t('Internal note')); ?></legend>
            <?php
            $internal_note = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $context->getDebtor()->get("internal_note"));
            ?>
            <p><?php autohtml($internal_note); ?></p>
        </fieldset>
    <?php endif; ?>

</div>

<div id="colTwo">
    <div class="box">
    <table>
        <caption><?php e(t('Contact information')); ?></caption>
        <tbody>
            <tr>
                <th><?php e(t('Number')); ?></th>
                <td><?php e($context->getDebtor()->contact->get("number")); ?> <a href="<?php e(url(null, array('edit_contact' => $context->getDebtor()->contact->get('id')))); ?>" class="edit"><?php e(t('Edit')); ?></a></td>
            </tr>
            <tr>
                <th><?php e(t('Contact')); ?></th>
                <td><a href="<?php //e($contact_module->getPath()); ?><?php e(url('../../../../contact/'. $context->getDebtor()->contact->get('id'))); ?>"><?php e($context->getDebtor()->contact->address->get("name")); ?></a></td>
            </tr>
            <tr>
                <th><?php e(t('Address')); ?></th>
                <td class="adr">
                    <div class="adr">
                        <div class="street-address"><?php autohtml($context->getDebtor()->contact->address->get("address")); ?></div>
                        <span class="postal-code"><?php e($context->getDebtor()->contact->address->get('postcode')); ?></span>  <span class="location"><?php e($context->getDebtor()->contact->address->get('city')); ?></span>
                        <div class="country"><?php e($context->getDebtor()->contact->address->get('country')); ?></div>
                    </div>
                </td>
            </tr>
            <?php if ($context->getDebtor()->get("type") == 'invoice' and $context->getDebtor()->contact->get('preferred_invoice') == 3 and $context->getDebtor()->contact->address->get('ean') and $context->isValidScanInContact()) : ?>
            <tr>
                <th><?php e(t('EAN-number')); ?></th>
                <td><?php e($context->getDebtor()->contact->address->get('ean')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php e(t('Email')); ?></th>
                <td><?php e($context->getDebtor()->contact->address->get("email")); ?></td>
            </tr>
            <?php if ($context->getDebtor()->contact->address->get("cvr") != '' && $context->getDebtor()->contact->address->get("cvr") != 0) : ?>
                <tr>
                    <th><?php e(t('CVR')); ?></th>
                    <td><?php e($context->getDebtor()->contact->address->get("cvr")); ?></td>
                </tr>
            <?php endif; ?>

            <?php if (isset($context->getDebtor()->contact_person) && strtolower(get_class($context->getDebtor()->contact_person)) == "contactperson") : ?>
                <tr>
                    <th><?php e(t('Att.')); ?></th>
                    <td><?php e($context->getDebtor()->contact_person->get("name")); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <?php if ($context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") == "sent") :  ?>
        <div class="box">
            <h2><?php e(t('Register payment')); ?></h2>
            <form method="post" action="<?php e(url('payment')); ?>">
                <?php
                // @TODO: hack as long as the payment types are not the same as on the invoice
                if ($context->getDebtor()->get('payment_method') == 2 || $context->getDebtor()->get('payment_method') == 3) {
                    $payment_method = 1; // giro
                } elseif ($context->getDebtor()->get('round_off')) {
                    $payment_method = 3; // cash
                } else {
                    $payment_method = 0; // bank_transfer
                }

                $payment = new Payment($context->getDebtor());
                $types = $payment->getTypes();
                ?>
                <input type="hidden" value="<?php e($context->getDebtor()->get('id')); ?>" name="id" />
                <input type="hidden" value="invoice" name="for" />
                <input type="hidden" name="amount" value="<?php e(number_format($context->getDebtor()->get("arrears"), 2, ",", ".")); ?>" />
                <input type="hidden" name="type" value="<?php e($payment_method); ?>" />

                <div>
                    <?php e(t('register')); ?> DKK <strong><?php e(number_format($context->getDebtor()->get("arrears"), 2, ",", ".")); ?></strong> <?php e(t('paid by')); ?> <strong><?php e(t($types[$payment_method])); ?></strong>:
                </div>

                <div class="formrow">
                    <label for="payment_date" class="tight"><?php e(t('Date')); ?></label>
                    <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" size="8" />
                </div>

                <div style="clear: both;">
                    <input class="confirm" type="submit" name="payment" value="<?php e(t('Register')); ?>" title="<?php e(t('This will register the payment')); ?>" />
                    <a href="<?php e(url('payment', array('for' => 'invoice'))); ?>"><?php e(t('give me more choices')); ?></a>.
                </div>
            </form>
            <p><a href="<?php e(url('depreciation')); ?>"><?php e(t('I am not going to recieve the full payment...')); ?></a></p>
        </div>
    <?php elseif ($context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") == 'executed') : ?>
        <div class="box">
            <a href="<?php e(url('payment')); ?>"><?php e(t('Register payment or reimbursement')); ?></a>.
        </div>

    <?php endif; ?>

</div>

<div style="clear: both">

    <?php
    if ($context->getDebtor()->get("type") == "invoice") {
        if ($context->getKernel()->user->hasModuleAccess('accounting')) {
            $module_accounting = $context->getKernel()->useModule('accounting');
        }

        $payments = $context->getDebtor()->getDebtorAccount()->getList();
        $payment_total = 0;
        if (count($payments) > 0) {
            ?>
                <table class="stripe">
                    <caption><?php e(t('Payments')); ?></caption>
                    <thead>
                        <tr>
                            <th><?php e(t('Date')); ?></th>
                            <th><?php e(t('Type')); ?></th>
                            <th><?php e(t('Description')); ?></th>
                            <th><?php e(t('Amount')); ?></th>
                            <?php if ($context->getKernel()->user->hasModuleAccess('accounting')) : ?>
                                <th><?php e(t('Stated')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($payments as $payment) {
                        $payment_total += $payment["amount"];
                        ?>
                        <tr>
                            <td><?php e($payment["dk_date"]); ?></td>
                            <td><?php e(t($payment['type'])); ?></td>
                            <td>
                                <?php
                                if ($payment["type"] == "credit_note") {
                                    ?>
                                    <a href="<?php e(url('../' . $payment["id"])); ?>"><?php e($payment["description"]); ?></a>
                                    <?php
                                } else {
                                    e($payment['description']);
                                }
                                ?>
                            </td>
                            <td class="amount"><?php e(number_format($payment["amount"], 2, ",", ".")); ?></td>
                            <?php if ($context->getKernel()->user->hasModuleAccess('accounting')) : ?>
                                <td>
                                    <?php if ($payment['is_stated']) : ?>
                                        <a href="<?php e(url('../../../../accounting/search', array('voucher_id' => $payment['voucher_id']))); ?>"><?php e(t('voucher')); ?></a>
                                    <?php elseif ($payment['type'] == 'credit_note') : ?>
                                        <a href="<?php e(url('../' . $payment['id'] . '/state')); ?>"><?php e(t('state credit note')); ?></a>
                                    <?php elseif ($payment['type'] == 'depreciation') : ?>
                                        <a href="<?php e(url('../' . $context->getDebtor()->get('id') . '/depreciation/'.$payment['id'].'/state')); ?>"><?php e(t('state depreciation')); ?></a>
                                    <?php else : ?>
                                        <a href="<?php e(url('../' . $context->getDebtor()->get('id') . '/payment/' . $payment['id'] . '/state')); ?>"><?php e(t('state payment')); ?></a>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><strong><?php e(t('Total')); ?></strong></td>
                        <td class="amount"><?php e(number_format($payment_total, 2, ",", ".")); ?></td>
                        <?php if ($context->getKernel()->user->hasModuleAccess('accounting')) : ?>
                            <td>&nbsp;</td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th><?php e(t('Missing payment')); ?></th>
                        <td class="amount"><?php e(number_format($context->getDebtor()->get("total") - $payment_total, 2, ",", ".")); ?></td>
                        <?php if ($context->getKernel()->user->hasModuleAccess('accounting')) : ?>
                            <td>&nbsp;</td>
                        <?php endif; ?>
                    </tr>
                </table>
            <?php
        }
    }
    ?>

    <?php

    if (($context->getDebtor()->get("type") == "order" || $context->getDebtor()->get("type") == "invoice") && $context->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
        $onlinepayment_module = $context->getKernel()->useModule('onlinepayment', true); // true: ignore user permisssion
        $onlinepayment = OnlinePayment::factory($context->getKernel());
        $onlinepayment->getDBQuery()->setFilter('belong_to', $context->getDebtor()->get("type"));
        $onlinepayment->getDBQuery()->setFilter('belong_to_id', $context->getDebtor()->get('id'));
        $actions = $onlinepayment->getTransactionActions();

        $payment_list = $onlinepayment->getlist();

        if (count($payment_list) > 0) {
            ?>
            <div class="box">
                <h2><?php e(t('Online payment')); ?></h2>

                <table class="stribe">
                    <thead>
                        <tr>
                            <th><?php e(t('Date')); ?></th>
                            <th><?php e(t('Transaction number')); ?></th>
                            <th><?php e(t('Status')); ?></th>
                            <th><?php e(t('Amount')); ?></th>
                            <th>&nbsp;</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payment_list as $p) : ?>
                            <tr>
                                <td><?php e($p['dk_date_created']); ?></td>
                                <td><?php e($p['transaction_number']); ?></td>
                                <td>
                                    <?php
                                    e(t($p['status'], 'onlinepayment'));
                                    if ($p['user_transaction_status_translated'] != "") {
                                        e(" (".$p['user_transaction_status_translated']);
                                        if ($p['pbs_status'] != '' && $p['pbs_status'] != '000') {
                                            e(": ".$p['pbs_status']);
                                        }
                                        e(")");
                                    } elseif ($p['status'] == 'authorized') { ?>
                                        (<acronym title="<?php e(t('Payment cannot be captured before the invoice has been sent')); ?>"><?php e(t('not captured')); ?></acronym>)
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td class="amount">
                                    <?php
                                    if ($p['currency'] && is_object($p['currency'])) {
                                        e($p['currency']->getType()->getIsoCode().' ');
                                    } elseif ($context->getKernel()->intranet->hasModuleAccess('currency')) {
                                        e('DKK ');
                                    }
                                    e($p['dk_amount']);
                                    ?>
                                </td>
                                <td class="options">

                                    <?php if (count($actions) > 0 && $p['status'] == "authorized" && $context->getKernel()->user->hasModuleAccess('onlinepayment')) : // Changed for better usability. $context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") == "sent"    ?>
                                        <?php
                                        foreach ($actions as $a) {
                                            if ($a['action'] != 'capture' || ($context->getDebtor()->get("type") == "invoice" && $context->getDebtor()->get("status") == "sent")) {
                                                ?>
                                                <a href="<?php e(url(null, array('onlinepayment_id' => $p['id'], 'onlinepayment_action' => $a['action']))); ?>" class="confirm"><?php e($a['label']); ?></a>
                                                <?php
                                            }
                                        }
                                        ?>
                                    <?php endif; ?>
                                    <?php if ($p['status'] == 'authorized') : ?>
                                        <a href="<?php e(url('onlinepayment/' . $p['id'])); ?>" class="edit"><?php e(t('Edit payment')); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        // paymentmethodkey 5 is onlinepayment
        } elseif ($context->getDebtor()->getPaymentMethodKey() == 5 and $context->getDebtor()->getWhereToId() == 0) {
            $payment_url = '<strong>Der findes ikke nogen url</strong>';
            try {
                $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($context->getDebtor()->getWhereFromId());
                if ($shop) {
                    $payment_url = $context->getDebtor()->getPaymentLink($shop->getPaymentUrl());
                }
            } catch (Doctrine_Record_Exeption $e) {
            }
            if ($shop and $shop->getPaymentUrl()) : ?>
                <div class="warning">
                    <?php e(t('An online payment should be present. Maybe the customer cancelled the buy, or an error occurred at your online payment provider. The customer can pay on the following link')); ?>:
                    <?php e($payment_url); ?>. <a href="<?php e(url(null, array('action' => 'send_onlinepaymentlink'))); ?>"><?php e('Write email'); ?></a>.
                </div>
            <?php                                                                                                                                                                                                                                                                                                                                     elseif ($shop === false and $context->getKernel()->user->hasModuleAccess('shop')) : ?>
                <div class="warning">
                    <?php e(t('An online payment should be present. However it has not been created from the shop. If you want to make it possible to pay online, you should create the order from your shop, edit it, and then return to this page and send the payment link to the customer')); ?>.
                </div>
            <?php else : ?>
                <div class="warning">
                    <?php e(t('An onlinepayment should be present. You can supply a payment link from the shop. Supplying a link would make it possible to automatically writing an email to the contact with the payment link')); ?>.
                </div>
            <?php endif;
        }
    }
    ?>

<div style="clear:both;">
    <?php if ($context->getDebtor()->get("locked") == false) { ?>
        <ul class="options" style="clear: both;">
            <li><a href="<?php e(url('selectmultipleproductwithquantity', array('set_quantity' => true, 'multiple' => true))); ?>"><?php e(t('Add item')); ?></a></li>
        </ul>
    <?php } ?>

    <table class="stripe" style="clear:both;">
        <caption><?php e(t('Products')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('Product number')); ?></th>
                <th><?php e(t('Description')); ?></th>
                <th colspan="2"><?php e(t('Quantity')); ?></th>
                <th><?php e(t('Price')); ?></th>
                <th><?php e(t('Amount')); ?></th>
                <?php if ($context->getKernel()->intranet->hasModuleAccess('currency') && false !== $context->getDebtor()->getCurrency()) : ?>
                    <th><?php e($context->getDebtor()->getCurrency()->getType()->getIsoCode()); ?></th>
                <?php endif; ?>
                <th>&nbsp;</th>
            </tr>
        </thead>


        <tbody>
            <?php
            // @todo we should do all the calculations with a calculator
            $items = $context->getDebtor()->getItems();
            $total = 0;
            $total_currency = 0;
            if (isset($items[0]["vat"])) {
                $vat = $items[0]["vat"]; // Er der moms p� det f�rste produkt
            } else {
                $vat = 0;
            }

            for ($i = 0, $max = count($items); $i<$max; $i++) {
                $total += $items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2);
                $vat = $items[$i]["vat"];
                ?>
                <tr id="i<?php e($items[$i]["id"]); ?>" <?php if (isset($_GET['item_id']) && $_GET['item_id'] == $items[$i]['id']) {
                    print(' class="fade"');
} ?>>
                    <td><?php e($items[$i]["number"]); ?></td>
                    <td><?php e($items[$i]["name"]); ?>
                        <?php
                        if ($items[$i]["description"] != "") {
                            autohtml($items[$i]["description"]);
                            if ($context->getDebtor()->get("locked") == false) {
                                echo '<br /> <a href="'.url('item/' . intval($items[$i]["id"]), array('edit')).'">'.t('Edit text').'</a>';
                            }
                        } elseif ($context->getDebtor()->get("locked") == false) {
                            echo ' <a href="'.url('item/' . intval($items[$i]["id"]), array('edit')).'">'.t('Add text').'</a>';
                        }

                        ?>
                    </td>
                    <?php
                    if ($items[$i]["unit"] != "") {
                        ?>
                        <td><?php e(number_format($items[$i]["quantity"], 2, ",", ".")); ?></td>
                        <td><?php e(t($items[$i]["unit"], 'product')); ?></td>
                        <td class="amount"><?php e($items[$i]["price"]->getAsLocal('da_dk', 2)); ?></td>
                        <?php
                    } else {
                        ?>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <?php
                    }
                    ?>
                    <?php $price = new Ilib_Variable_Float($items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2)); ?>
                    <td class="amount"><?php e($price->getAsLocal('da_dk', 2)); ?></td>
                    <?php if ($context->getDebtor()->getCurrency()) : ?>
                        <?php
                        $price_currency = new Ilib_Variable_Float($items[$i]["quantity"]*$items[$i]["price_currency"]->getAsIso(2));
                        $total_currency += $price_currency->getAsIso(2);
                        ?>
                        <td class="amount"><?php e($price_currency->getAsLocal('da_dk', 2)); ?></td>
                    <?php endif; ?>
                    <td class="options">
                        <?php
                        if ($context->getDebtor()->get("locked") == false) {
                            ?>
                            <a class="moveup" href="<?php e(url(null, array('action' => 'moveup', 'item_id' => $items[$i]["id"]))); ?>"><?php e(t('Up')); ?></a>
                            <a class="movedown" href="<?php e(url(null, array('action' => 'movedown', 'item_id' => $items[$i]["id"]))); ?>"><?php e(t('Down')); ?></a>
                            <a class="edit" href="<?php e(url('item/' . $items[$i]["id"], array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                            <a class="delete" title="Dette vil slette varen!" href="<?php e(url(null, array('action' => 'delete_item', 'item_id' => $items[$i]["id"]))); ?>"><?php e(t('Delete')); ?></a>
                            <?php
                        }
                        ?>&nbsp;
                    </td>
                </tr>
                <?php

                if (($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
                    // Hvis der er moms p� nuv�rende produkt, men n�ste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><b>25% moms af <?php e(number_format($total, 2, ",", ".")); ?></b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="amount"><b><?php e(number_format($total * 0.25, 2, ",", ".")); ?></b></td>
                        <?php if ($context->getDebtor()->getCurrency()) : ?>
                            <td class="amount"><b><?php e(number_format($total_currency * 0.25, 2, ",", ".")); ?></b></td>
                            <?php $total_currency *= 1.25; ?>
                        <?php endif; ?>

                        <td>&nbsp;</td>
                    </tr>
                    <?php
                    $total = $total * 1.25;
                }
            }
            ?>
        </tbody>
        <?php if ($context->getDebtor()->get("round_off") == 1 && $context->getDebtor()->get("type") == "invoice" && $total != $context->getDebtor()->get("total")) { ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3"><?php e(t('Total')); ?></td>
                <td class="amount"><?php e(number_format($total, 2, ",", ".")); ?></td>
                <?php if ($context->getDebtor()->getCurrency()) : ?>
                    <td class="amount"><?php e(number_format($total_currency, 2, ",", ".")); ?></td>
                <?php endif; ?>

                <td>&nbsp;</td>
            </tr>
            <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3"><b>Total<?php if ($context->getDebtor()->get("round_off") == 1 && $context->getDebtor()->get("type") == "invoice" && $total != $context->getDebtor()->get("total")) {
                e(" afrundet");
} ?>:</b></td>
            <td class="amount"><strong><?php e(number_format($context->getDebtor()->get("total"), 2, ",", ".")); ?></strong></td>
            <?php if ($context->getDebtor()->getCurrency()) : ?>
                <td class="amount"><strong><?php e(number_format($total_currency, 2, ",", ".")); ?></strong></td>
            <?php endif; ?>

            <td>&nbsp;</td>
        </tr>
    </table>
</div>
</div>

<?php
if ($context->getKernel()->user->hasModuleAccess('invoice')) :
    $context->getKernel()->useModule('invoice');
    $reminder = new Reminder($context->getKernel());
    $reminder->getDBQuery()->setFilter("invoice_id", $context->getDebtor()->get("id"));
    $reminders = $reminder->getList();
    if (count($reminders) > 0) :
        ?>
        <table class="stripe">
        <caption><?php e(t('Reminders on this invoice')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('No.')); ?></th>
        <th><?php e(t('Contact')); ?></th>
        <th><?php e(t('Description')); ?></th>
        <th><?php e(t('Sent')); ?></th>
        <th><?php e(t('Sent as')); ?></th>
        <th><?php e(t('Due date')); ?></th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($reminders as $reminder) {
        ?>
        <tr id="i<?php e($reminder["id"]); ?>"<?php if (isset($_GET['id']) && $_GET['id'] == $reminder['id']) {
            print(" class=\"fade\"");
} ?>>
            <td class="number"><?php e($reminder["number"]); ?></td>
            <td><a href="<?php e(url('../../../reminders', array('contact_id' => $reminder["contact_id"]))); ?>"><?php e($reminder["name"]); ?></a></td>
            <td><a href="<?php e(url('../../../reminders/' . $reminder["id"])); ?>"><?php (trim($reminder["description"] != "")) ? e($reminder["description"]) : e('['.t("No description").']'); ?></a></td>
            <td class="date">
                <?php
                if ($reminder["status"] != "created") {
                    e($reminder["dk_date_sent"]);
                } else {
                    e(t('No'));
                }
                ?>
      </td>
            <td><?php e($reminder["send_as"]); ?></td>
            <td class="date">
                <?php
                if ($reminder["status"] == "executed" || $reminder["status"] == "canceled") {
                    e($reminder["status"]);
                } elseif ($reminder["due_date"] < date("Y-m-d")) { ?>
                    <span class="red"><?php e($reminder["dk_due_date"]); ?></span>
                <?php
                } else {
                    e($reminder["dk_due_date"]);
                }
                ?>
            </td>
            <td class="buttons">
                <?php
                if ($reminder["locked"] == 0) {
                    ?>
                    <a class="edit" href="<?php e(url('../../../reminders/' . $reminder["id"], array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                    <?php if ($reminder["status"] == "created") : ?>
                    <a class="delete" href="<?php e(url('../../../reminders/' . $reminder["id"], array('delete'))); ?>"><?php e(t('Delete')); ?></a>
                    <?php endif; ?>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
    </table>
    <?php                                                                                                         endif;
endif; ?>
