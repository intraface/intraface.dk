
<h1><?php e(__('Settings')); ?></h1>

<?php if (isset($error)) echo $error->view(); ?>

<form action="<?php e(url()); ?>" method="post">

    <fieldset>
        <legend>Kontaktperson p� PDF og afsender af e-mail</legend>

            <div class="formrow">
                <label for="debtor_sender">Konaktperson/Afsender</label>
                <select name="debtor_sender">
                    <option value="intranet" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'intranet') echo 'selected="selected"'; ?> >Intranetoplysninger (<?php e($kernel->intranet->address->get('name').' / '.$kernel->intranet->address->get('email')); ?>)</option>
                    <option value="user" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'user') echo 'selected="selected"'; ?> >Aktuel brugers oplysninger (<?php e($kernel->user->getAddress()->get('name').' / '.$kernel->user->getAddress()->get('email')); ?>)</option>
                    <option value="defined" <?php if (isset($values['debtor_sender']) && $values['debtor_sender'] == 'defined') echo 'selected="selected"'; ?> >Brugerdefineret... (Udfyld herunder)</option>
                </select>
            </div>


            <div class="formrow">
                <label for="debtor_sender_name">Afsendernavn</label>
                <input type="text" name="debtor_sender_name" id="debtor_sender_name" value="<?php if (!empty($values['debtor_sender_name'])) e($values['debtor_sender_name']); ?>" />
            </div>
            <div class="formrow">
                <label for="debtor_sender_email">Afsender e-mail</label>
                <input type="text" name="debtor_sender_email" id="debtor_sender_email" value="<?php if (!empty($values['debtor_sender_email'])) e($values['debtor_sender_email']); ?>" />
            </div>
    </fieldset>

    <fieldset>
        <legend>Bankoplysninger</legend>
        <fieldset>
            <legend>Kontobetaling:</legend>
            <div class="formrow">
                <label for="bankname">Bank</label>
                <input type="text" name="bank_name" id="bankname" value="<?php e($values['bank_name']); ?>" />
            </div>
            <div class="formrow">
                <label for="regnumber">Registreringsnummer</label>
                <input type="text" name="bank_reg_number" id="regnumber" value="<?php e($values['bank_reg_number']); ?>" />
            </div>
            <div class="formrow">
                <label for="accountnumber">Kontonummer</label>
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
        <legend>E-mail til L�s-ind bureau</legend>
        <p><?php e($string); ?></p>

        <?php if (!empty($scan_in_contact) AND is_object($scan_in_contact) AND !$scan_in_contact->address->get('email')): ?>
        <p class="warning">Der er ikke angivet nogen e-mail-adresse p� L�s-ind bureauet.</p>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name')); ?>
        <input type="submit" name="edit_scan_in_contact" value="Rediger" />
        <input type="hidden" name="scan_in_contact" value="<?php e($scan_in_contact->get('id')); ?>" />
        <?php elseif (empty($scan_in_contact) OR !is_object($scan_in_contact)): ?>
        <input type="submit" name="add_scan_in_contact" value="V�lg kontakt" />
        <?php else: ?>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name') . ' <'.$scan_in_contact->address->get('email').'>'); ?> <input type="submit" name="delete_scan_in_contact" value="Slet kontakt" />
        <?php endif; ?>
    </fieldset>

    <fieldset>
        <legend>Fast tekst p� ordre e-mail</legend>
        <textarea name="order_email_text" cols="80" rows="8"><?php e($values['order_email_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend>Fast tekst p� fakturaer</legend>
        <textarea name="invoice_text" cols="80" rows="8"><?php e($values['invoice_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend>Tekst p� rykker</legend>
        <textarea name="reminder_text" cols="80" rows="8"><?php e($values['reminder_text']); ?></textarea>
    </fieldset>

    <div>
        <input type="submit" name="submit" value="<?php e(t('Save')); ?>" />
	    <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>
</form>