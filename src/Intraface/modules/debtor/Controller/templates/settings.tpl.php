<?php
$values = $context->getValues();
?>
<h1><?php e(t('Settings')); ?></h1>

<?php echo $context->getError()->view(); ?>

<form action="<?php e(url()); ?>" method="post">

    <fieldset>
        <legend><?php e(t('Contact to show on correspondance')); ?></legend>

            <div class="formrow">
                <label for="debtor_sender"><?php e(t('Contactperson / Sender')); ?></label>
                <select name="debtor_sender">
                    <option value="intranet" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'intranet') echo 'selected="selected"'; ?> >Intranetoplysninger (<?php e($kernel->intranet->address->get('name').' / '.$kernel->intranet->address->get('email')); ?>)</option>
                    <option value="user" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'user') echo 'selected="selected"'; ?> >Aktuel brugers oplysninger (<?php e($kernel->user->getAddress()->get('name').' / '.$kernel->user->getAddress()->get('email')); ?>)</option>
                    <option value="defined" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'defined') echo 'selected="selected"'; ?> >Brugerdefineret... (Udfyld herunder)</option>
                </select>
            </div>


            <div class="formrow">
                <label for="debtor_sender_name"><?php e(t('Sender name')); ?></label>
                <input type="text" name="debtor_sender_name" id="debtor_sender_name" value="<?php if (!empty($values['debtor_sender_name'])) e($values['debtor_sender_name']); ?>" />
            </div>
            <div class="formrow">
                <label for="debtor_sender_email"><?php e(t('Sender email')); ?></label>
                <input type="text" name="debtor_sender_email" id="debtor_sender_email" value="<?php if (!empty($values['debtor_sender_email'])) e($values['debtor_sender_email']); ?>" />
            </div>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Bank information')); ?></legend>
        <fieldset>
            <legend><?php e(t('Wire transfer')); ?></legend>
            <div class="formrow">
                <label for="bankname"><?php e(t('Bank')); ?></label>
                <input type="text" name="bank_name" id="bankname" value="<?php e($values['bank_name']); ?>" />
            </div>
            <div class="formrow">
                <label for="regnumber"><?php e(t('Registration number')); ?></label>
                <input type="text" name="bank_reg_number" id="regnumber" value="<?php e($values['bank_reg_number']); ?>" />
            </div>
            <div class="formrow">
                <label for="accountnumber"><?php e(t('Account number')); ?></label>
                <input type="text" name="bank_account_number" id="accountnumber" value="<?php e($values['bank_account_number']); ?>" />
            </div>
        </fieldset>
        <fieldset>
            <legend>Girobetaling:</legend>
            <div class="formrow">
                <label for="giroaccountnumber">Girokontonummer</label>
                <input type="text" name="giro_account_number" id="giroaccountnumber" value="<?php e($values['giro_account_number']); ?>" />
            </div>
        </fieldset>
    </fieldset>

    <fieldset>
        <legend><?php e(t('E-mail to data scanning agency')); ?></legend>
        <p><?php e($string); ?></p>

        <?php if (!empty($scan_in_contact) AND is_object($scan_in_contact) AND !$scan_in_contact->address->get('email')): ?>
        <p class="warning"><?php e(t('You have not supplied an email for the data scanning agency.')); ?></p>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name')); ?>
        <input type="submit" name="edit_scan_in_contact" value="<?php e(t('Edit')); ?>" />
        <input type="hidden" name="scan_in_contact" value="<?php e($scan_in_contact->get('id')); ?>" />
        <?php elseif (empty($scan_in_contact) OR !is_object($scan_in_contact)): ?>
        <input type="submit" name="add_scan_in_contact" value="<?php e(t('Choose contact')); ?>" />
        <?php else: ?>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name') . ' <'.$scan_in_contact->address->get('email').'>'); ?> <input type="submit" name="delete_scan_in_contact" value="Slet kontakt" />
        <?php endif; ?>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Default text on order emails')); ?></legend>
        <textarea name="order_email_text" cols="80" rows="8"><?php e($values['order_email_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Default text on invoices')); ?></legend>
        <textarea name="invoice_text" cols="80" rows="8"><?php e($values['invoice_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Default text on invoice emails')); ?></legend>
        <textarea name="invoice_email_text" cols="80" rows="8"><?php if (!empty($values['invoice_email_text'])) e($values['invoice_email_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend><?php e(t('Default text on reminders')); ?></legend>
        <textarea name="reminder_text" cols="80" rows="8"><?php e($values['reminder_text']); ?></textarea>
    </fieldset>

	<!--
    <fieldset>
        <legend><?php e(t('Notifications')); ?></legend>
        <label>
        <input name="notify_order" value="1" type="checkbox" />
        	<?php e(t('Notify me via email on new orders')); ?>
        </label>
    </fieldset>
 	-->

    <div>
        <input type="submit" name="submit" value="<?php e(t('Save')); ?>" />
	    <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>
