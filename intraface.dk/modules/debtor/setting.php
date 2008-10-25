<?php
/**
 * Her skal kunne indstilles hvilke betalingsformer der kan bruges i forbindelse
 * med debtor-modulet. Det skal altså være noget, man tilføjer.
 *
 * Dvs. man tilføjer fx girokontobetaling - og så skal man indtaste oplysninger om det
 * Tilføjer man bankoverførsel, skal man indtaste bankoplysninger
 * Der skal laves noget lidt smartere med læs-ind-bureau og elektronisk faktura
 * Tekst på rykkere skal måske differentieres, så der er standardtekster til forskellige rykkere
 *
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 *
 */
require('../../include_first.php');
require('HTTP/Request.php');

$debtor_module = $kernel->module('debtor');
$kernel->useModule('invoice');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST)) {


        $error = new Intraface_Error;
        $validator = new Intraface_Validator($error);

        if ($_POST['debtor_sender'] == 'defined') {
            $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail');
            $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name');
        }
        else {
            $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail', 'allow_empty');
            $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name', '', 'allow_empty');

        }

        if (!$error->isError()) {

            $kernel->setting->set('intranet', 'debtor.sender', $_POST['debtor_sender']);

            $kernel->setting->set('intranet', 'debtor.sender.email', $_POST['debtor_sender_email']);
            $kernel->setting->set('intranet', 'debtor.sender.name', $_POST['debtor_sender_name']);
        }

        // reminder
        $kernel->setting->set('intranet', 'reminder.first.text', $_POST['reminder_text']);
        $kernel->setting->set('intranet', 'debtor.invoice.text', $_POST['invoice_text']);
        $kernel->setting->set('intranet', 'debtor.order.email.text', $_POST['order_email_text']);


        // bank
        $kernel->setting->set('intranet', 'bank_name', $_POST['bank_name']);
        $kernel->setting->set('intranet', 'bank_reg_number', $_POST['bank_reg_number']);
        $kernel->setting->set('intranet', 'bank_account_number', $_POST['bank_account_number']);
        $kernel->setting->set('intranet', 'giro_account_number', $_POST['giro_account_number']);
    }

    if (!empty($_POST['delete_scan_in_contact'])) {
        $kernel->setting->set('intranet', 'debtor.scan_in_contact', 0);

        header('Location: setting.php');
        exit;
    }

    elseif (isset($_POST['add_scan_in_contact'])) {
        if ($kernel->user->hasModuleAccess('contact')) {
            $contact_module = $kernel->useModule('contact');

            $redirect = Intraface_Redirect::factory($kernel, 'go');
            $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $debtor_module->getPath()."setting.php");
            $redirect->askParameter('contact_id');
            $redirect->setIdentifier('contact');

            if ($kernel->setting->get('intranet', 'debtor.scan_in_contact') > 0) {
                header("Location: ".$url . '?contact_id='.$kernel->setting->get('intranet', 'debtor.scan_in_contact'));
            }
            else {
                header("Location: ".$url );
            }
            exit;
        }
        else {
            trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
        }
    }


    elseif (isset($_POST['edit_scan_in_contact'])) {
        if ($kernel->user->hasModuleAccess('contact')) {
            $contact_module = $kernel->useModule('contact');

            $redirect = Intraface_Redirect::factory($kernel, 'go');
            $url = $redirect->setDestination($contact_module->getPath()."contact_edit.php?id=".intval($_POST['scan_in_contact']), $debtor_module->getPath()."setting.php");
            header("location: ".$url );
            exit;


        }
        else {
            trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
        }
    }

    if (!$error->isError()) {
        header('Location: index.php'); // Changed from setting.php, but don't know what is most right /SJ (14/1 2007)
        exit;
    }
    $values = $_POST;

}
else {

    if (isset($_GET['return_redirect_id'])) {
        $redirect = Intraface_Redirect::factory($kernel, 'return');
        if ($redirect->get('identifier') == 'contact') {
            // would be better if the return were a post
            $kernel->setting->set('intranet', 'debtor.scan_in_contact', $redirect->getParameter('contact_id'));
        }
    }

    // find settings frem
    $values['debtor_sender'] = $kernel->setting->get('intranet', 'debtor.sender');
    $values['debtor_sender_email'] = $kernel->setting->get('intranet', 'debtor.sender.email');
    $values['debtor_sender_name'] = $kernel->setting->get('intranet', 'debtor.sender.name');
    $values['bank_name'] = $kernel->setting->get('intranet', 'bank_name');
    $values['bank_reg_number'] = $kernel->setting->get('intranet', 'bank_reg_number');
    $values['bank_account_number'] = $kernel->setting->get('intranet', 'bank_account_number');
    $values['giro_account_number'] = $kernel->setting->get('intranet', 'giro_account_number');
    $values['reminder_text'] = $kernel->setting->get('intranet', 'reminder.first.text');
    $values['invoice_text'] = $kernel->setting->get('intranet', 'debtor.invoice.text');
    $values['order_email_text'] = $kernel->setting->get('intranet', 'debtor.order.email.text');


    if ($kernel->setting->get('intranet', 'debtor.scan_in_contact') > 0) {
        $scan_in_contact = new Contact($kernel, $kernel->setting->get('intranet', 'debtor.scan_in_contact'));
    }


    //$values['preferred_payment_method'] = $kernel->setting->get('intranet', 'debtor.preferred_payment_method');

}

// tjekker om det er gratis at bruge læs ind
// Dette kunne være smart hvis vi kunne få det til at virke
/*
$req =& new HTTP_Request('http://www.eogs.dk/sw7483.asp', array('timeout' => 2));
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addPostData('FIELD_343_id', $kernel->intranet->address->get('cvr'));
$req->addPostData('lFormId', '226');
*/
//!PEAR::isError($req->sendRequest())
if (1==2) {
     $response = utf8_decode(strip_tags($req->getResponseBody()));
     print $response;
     if (strpos($response, 'Anfører din virksomhed dette CVR/SE-nummer på sine regninger, kan disse sendes via Læs Ind-bureau gratis.')) {
        $string = 'Det er gratis for din virksomhed at bruge et Læs-ind bureau.';
     }
     else {
        $string = 'Det ser ud til, at det koster penge for dig at bruge et Læs-ind bureau. <a href="http://www.eogs.dk/sw7483.asp">Tjek det selv hos Erhvervs- og Selskabsstyrelsen</a>.';
     }

}
else {
    $string = 'Det er gratis for små og mellemstore virksomheder at bruge Læs-ind bureauer. <a href="http://www.eogs.dk/sw7483.asp">Tjek her om det gælder for din virksomhed</a>.';
}

$page = new Intraface_Page($kernel);
$page->start('Indstillinger');
?>

<h1>Indstillinger</h1>

<?php if (isset($error)) echo $error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

    <fieldset>
        <legend>Kontaktperson på PDF og afsender af e-mail</legend>

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
        <legend>E-mail til Læs-ind bureau</legend>
        <p><?php e($string); ?></p>

        <?php if (!empty($scan_in_contact) AND is_object($scan_in_contact) AND !$scan_in_contact->address->get('email')): ?>
        <p class="warning">Der er ikke angivet nogen e-mail-adresse på Læs-ind bureauet.</p>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name')); ?>
        <input type="submit" name="edit_scan_in_contact" value="Rediger" />
        <input type="hidden" name="scan_in_contact" value="<?php e($scan_in_contact->get('id')); ?>" />
        <?php elseif (empty($scan_in_contact) OR !is_object($scan_in_contact)): ?>
        <input type="submit" name="add_scan_in_contact" value="Vælg kontakt" />
        <?php else: ?>
        <strong>Kontakt</strong>: <?php e($scan_in_contact->get('name') . ' <'.$scan_in_contact->address->get('email').'>'); ?> <input type="submit" name="delete_scan_in_contact" value="Slet kontakt" />
        <?php endif; ?>
    </fieldset>

    <fieldset>
        <legend>Fast tekst på ordre e-mail</legend>
        <textarea name="order_email_text" cols="80" rows="8"><?php e($values['order_email_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend>Fast tekst på fakturaer</legend>
        <textarea name="invoice_text" cols="80" rows="8"><?php e($values['invoice_text']); ?></textarea>
    </fieldset>

    <fieldset>
        <legend>Tekst på rykker</legend>
        <textarea name="reminder_text" cols="80" rows="8"><?php e($values['reminder_text']); ?></textarea>
    </fieldset>

    <div>
        <input type="submit" name="submit" value="Gem" /> eller <a href="<?php e('index.php'); ?>">Fortryd</a>
    </div>
</form>

<?php
$page->end();
?>
