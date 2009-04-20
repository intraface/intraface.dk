<?php
/**
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */
require '../../include_first.php';

$debtor_module = $kernel->module('debtor');
$translation = $kernel->getTranslation('debtor');
$contact_module = $kernel->getModule('contact');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $debtor = Debtor::factory($kernel, intval($_POST['id']));

    // slet debtoren
    if (!empty($_POST['delete'])) {
        $type = $debtor->get("type");
        $debtor->delete();
        header("Location: list.php?type=".$type."&amp;use_stored=true");
        exit;
    } elseif (!empty($_POST['send_electronic_invoice'])) {
        header('Location: send.php?send=electronic_email&id=' . intval($debtor->get('id')));
        exit;
    } elseif (!empty($_POST['send_email'])) {
        header('Location: send.php?send=email&id=' . intval($debtor->get('id')));
        exit;

    }

    // annuller ordre tilbud eller order
    elseif (!empty($_POST['cancel']) AND ($debtor->get("type") == "quotation" || $debtor->get("type") == "order") && ($debtor->get('status') == "created" || $debtor->get('status') == "sent")) {
        $debtor->setStatus('cancelled');
    }

    // sæt status til sendt
    elseif (!empty($_POST['sent'])) {
        $debtor->setStatus('sent');

        if (($debtor->get("type") == 'credit_note' || $debtor->get("type") == 'invoice') AND $kernel->user->hasModuleAccess('accounting')) {
            header('location: state_'.$debtor->get('type').'.php?id=' . intval($debtor->get("id")));
        }
    }


    // Overføre tilbud til ordre
    elseif (!empty($_POST['order'])) {
        if ($kernel->user->hasModuleAccess('order') && $debtor->get("type") == "quotation") {
            $kernel->useModule("order");
            $order = new Order($kernel);
            if ($id = $order->create($debtor)) {
                header('Location: view.php?id='.$id);
                exit;
            }
        }
    }

    // Overføre ordre til faktura
    elseif (!empty($_POST['invoice'])) {
        if ($kernel->user->hasModuleAccess('invoice') && ($debtor->get("type") == "quotation" || $debtor->get("type") == "order")) {
            $kernel->useModule("invoice");
            $invoice = new Invoice($kernel);
            if ($id = $invoice->create($debtor)) {
                header('Location: view.php?id='.$id);
                exit;
            }
        }
    }

    // Overfør til kreditnota
    elseif (!empty($_POST['credit_note'])) {
        if ($kernel->user->hasModuleAccess('invoice') && $debtor->get("type") == "invoice") {
            $credit_note = new CreditNote($kernel);

            if ($id = $credit_note->create($debtor)) {
                header('Location: view.php?id='.$id);
                exit;
            }
        }
    }

    // cancel onlinepayment
    elseif (isset($_POST['onlinepayment_cancel']) && $kernel->user->hasModuleAccess('onlinepayment')) {
        $onlinepayment_module = $kernel->useModule('onlinepayment');
        $onlinepayment = OnlinePayment::factory($kernel, 'id', intval($_POST['onlinepayment_id']));

        $onlinepayment->setStatus('cancelled');
        $debtor->load();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $debtor = Debtor::factory($kernel, intval($_GET["id"]));

     if (isset($_GET["action"]) && $_GET["action"] == "send_onlinepaymentlink") {

        $shared_email = $kernel->useShared('email');
        if ($debtor->getPaymentMethodKey() == 5 AND $debtor->getWhereToId() == 0) {
            try {
                echo $debtor->getWhereFromId();
                $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($debtor->getWhereFromId());
                if ($shop) {
                    $payment_url = $debtor->getPaymentLink($shop->getPaymentUrl());
                }
            } catch (Doctrine_Record_Exeption $e) {
                trigger_error('Could not send an e-mail with onlinepayment-link', E_USER_ERROR);
            }
        }

        if ($kernel->intranet->get("pdf_header_file_id") != 0) {
            $file = new FileHandler($kernel, $kernel->intranet->get("pdf_header_file_id"));
        } else {
            $file = NULL;
        }

        $body = 'Tak for din bestilling i vores onlineshop. Vi har ikke registreret nogen onlinebetaling sammen med bestillingen, hvilket kan skyldes flere ting.

1) Du fortrudt bestillingen, da du skulle til at betale. I så fald må du meget gerne skrive tilbage og annullere din bestilling.
2) Der er sket en fejl under betalingen. I det tilfælde må du gerne betale ved at gå ind på nedenstående link:

' .  $payment_url;
        $subject = 'Betaling ikke modtaget';

        // gem debtoren som en fil i filsystemet
        $filehandler = new FileHandler($kernel);
        $tmp_file = $filehandler->createTemporaryFile($translation->get($debtor->get("type")).$debtor->get('number').'.pdf');

        // Her gemmes filen
        $report = new Intraface_modules_debtor_Visitor_Pdf($translation, $file);
        $report->visit($debtor, $onlinepayment);
        $report->output('file', $tmp_file->getFilePath());

        // gem filen med filehandleren
        $filehandler = new FileHandler($kernel);
        if (!$file_id = $filehandler->save($tmp_file->getFilePath(), $tmp_file->getFileName(), 'hidden', 'application/pdf')) {
            echo $filehandler->error->view();
            trigger_error('Filen kunne ikke gemmes', E_USER_ERROR);
        }

        $input['accessibility'] = 'intranet';
        if (!$file_id = $filehandler->update($input)) {
            echo $filehandler->error->view();
            trigger_error('Oplysninger om filen kunne ikke opdateres', E_USER_ERROR);
        }

        switch($kernel->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                $from_email = '';
                $from_name = '';
                break;
            case 'user':
                $from_email = $kernel->user->getAddress()->get('email');
                $from_name = $kernel->user->getAddress()->get('name');
                break;
            case 'defined':
                $from_email = $kernel->setting->get('intranet', 'debtor.sender.email');
                $from_name = $kernel->setting->get('intranet', 'debtor.sender.name');
                break;
            default:
                trigger_error("Invalid sender!", E_USER_ERROR);
                exit;
        }
        $contact = new Contact($kernel, $debtor->get('contact_id'));
        // opret e-mailen
        $email = new Email($kernel);
        if (!$email->save(array(
                'contact_id' => $contact->get('id'),
                'subject' => $subject,
                'body' => $body . "\n\n--\n" . $kernel->user->getAddress()->get('name') . "\n" . $kernel->intranet->get('name'),
                'from_email' => $from_email,
                'from_name' => $from_name,
                'type_id' => 10, // electronic invoice
                'belong_to' => $debtor->get('id')
            ))) {
            echo $email->error->view();
            exit;
            trigger_error('E-mailen kunne ikke gemmes', E_USER_ERROR);
        }

        // tilknyt fil
        if (!$email->attachFile($file_id, $filehandler->get('file_name'))) {
            echo $email->error->view();
            trigger_error('Filen kunne ikke vedhæftes', E_USER_ERROR);
        }

        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $shared_email = $kernel->useShared('email');

        // First vi set the last, because we need this id to the first.
        $url = $redirect->setDestination($shared_email->getPath().'edit.php?id='.$email->get('id'), $debtor_module->getPath().'view.php?id='.$debtor->get('id'));
        $redirect->setIdentifier('send_onlinepaymentlink');
        $redirect->askParameter('send_onlinepaymentlink_status');

        header('Location: ' . $url);
        exit;

    }


    // delete item
    if (isset($_GET["action"]) && $_GET["action"] == "delete_item") {
        $debtor->loadItem(intval($_GET["item_id"]));
        $debtor->item->delete();
        header('Location: view.php?id='. $debtor->getId());
        exit;
    }
    // move item
    if (isset($_GET['action']) && $_GET['action'] == "moveup") {
        $debtor->loadItem(intval($_GET['item_id']));
        $debtor->item->getPosition(MDB2::singleton(DB_DSN))->moveUp();
    }

    // move item
    if (isset($_GET['action']) && $_GET['action'] == "movedown") {
        $debtor->loadItem(intval($_GET['item_id']));
        $debtor->item->getPosition(MDB2::singleton(DB_DSN))->moveDown();
    }

    // registrere onlinepayment
    if ($kernel->user->hasModuleAccess('onlinepayment') && isset($_GET['onlinepayment_action']) && $_GET['onlinepayment_action'] != "") {
        if ($_GET['onlinepayment_action'] != 'capture' || ($debtor->get("type") == "invoice" && $debtor->get("status") == "sent")) {
            $onlinepayment_module = $kernel->useModule('onlinepayment'); // true: ignore user permisssion
            $onlinepayment = OnlinePayment::factory($kernel, 'id', intval($_GET['onlinepayment_id']));

            if (!$onlinepayment->transactionAction($_GET['onlinepayment_action'])) {
                $onlinepayment_show_cancel_option = true;
            }

            $debtor->load();

            // @todo vi skulle faktisk kun videre, hvis det ikke er
            // en tilbagebetaling eller hvad?
            if ($debtor->get("type") == "invoice" && $debtor->get("status") == "sent" AND !$onlinepayment->error->isError()) {
                if ($kernel->user->hasModuleAccess('accounting')) {
                    header('location: state_payment.php?for=invoice&id=' . intval($debtor->get("id")).'&payment_id='.$onlinepayment->get('created_payment_id'));
                    exit;
                }
            }
        }
    }

    // edit contact
    if (isset($_GET['edit_contact'])) {
        $contact_module = $kernel->getModule('contact');
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($contact_module->getPath().'contact_edit.php?id='.intval($debtor->contact->get('id')), $debtor_module->getPath().'view.php?id='.$debtor->get('id'));
        header('location: '.$url);
        exit;
    }

    // Redirect til tilføj produkt
    if (isset($_GET['add_item'])) {
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $product_module = $kernel->useModule('product');
        $redirect->setIdentifier('add_item');
        $url = $redirect->setDestination($product_module->getPath().'select_product.php?set_quantity=1', $debtor_module->getPath().'view.php?id='.$debtor->get('id'));
        $redirect->askParameter('product_id', 'multiple');
        header('Location: '.$url);
        exit;
    }


    // Return fra tilføj produkt og send email
    if (isset($_GET['return_redirect_id'])) {
        $return_redirect = Intraface_Redirect::factory($kernel, 'return');

        if ($return_redirect->get('identifier') == 'add_item') {
            $selected_products = $return_redirect->getParameter('product_id', 'with_extra_value');
            foreach ($selected_products as $product) {
                $debtor->loadItem();
                $product['value'] = unserialize($product['value']);
                $debtor->item->save(array('product_id' => $product['value']['product_id'], 'product_variation_id' => $product['value']['product_variation_id'], 'quantity' => $product['extra_value'], 'description' => ''));
            }
            $return_redirect->delete();
            $debtor->load();
        } elseif ($return_redirect->get('identifier') == 'send_email') {
            if ($return_redirect->getParameter('send_email_status') == 'sent' OR $return_redirect->getParameter('send_email_status') == 'outbox') {
                $email_send_with_success = true;
                // hvis faktura er genfremsendt skal den ikke sætte status igen
                if ($debtor->get('status') != 'sent') {
                    $debtor->setStatus('sent');
                }
                $return_redirect->delete();

                if (($debtor->get("type") == 'credit_note' || $debtor->get("type") == 'invoice') AND !$debtor->isStated() AND $kernel->user->hasModuleAccess('accounting')) {
                    header('location: state_'.$debtor->get('type').'.php?id=' . intval($debtor->get("id")));
                }
            }

        }
    }
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'view.js');
$page->start($translation->get($debtor->get('type')));
?>

<div id="colOne">
<div class="box">
    <h1><?php e(t($debtor->get("type"))); ?> #<?php e($debtor->get("number")); ?></h1>


    <ul class="options">
        <?php if ($debtor->get("locked") == false): ?>
            <li><a href="edit.php?id=<?php e($debtor->get("id")); ?>">Ret</a></li>
        <?php endif; ?>
        <li><a class="pdf" href="pdf_viewer.php?id=<?php e($debtor->get("id")); ?>" target="_blank">Udskriv PDF</a></li>
        <li><a href="list.php?id=<?php e($debtor->get("id")); ?>&amp;type=<?php e($debtor->get("type")); ?>&amp;use_stored=true">Luk</a></li>
    </ul>

    <p><?php e($debtor->get('description')); ?></p>
</div>

<?php echo $debtor->error->view(); ?>
<?php
// onlinepayment error viewing, also with showing cancel onlinepayment button.
if (isset($onlinepayment)) {
    echo $onlinepayment->error->view();
    if (isset($onlinepayment_show_cancel_option) && $onlinepayment_show_cancel_option == true) {
        echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'"><ul class="formerrors"><li>Ønsker du i stedet at <input type="submit" name="onlinepayment_cancel" value="Annullere" /><input type="hidden" name="id" value="'.$debtor->get('id').'" /><input type="hidden" name="onlinepayment_id" value="'.$onlinepayment->id.'" /> registreringen af betalingen.</li></ul></form>';
    }
}
?>
<?php if ($kernel->intranet->get("pdf_header_file_id") == 0 && $kernel->user->hasModuleAccess('administration')): ?>
    <div class="message-dependent">
        <p><a href="<?php e(url('/main/controlpanel/intranet.php')); ?>">Upload et logo</a> til dine pdf'er.</p>
    </div>
<?php endif; ?>

<?php if ($debtor->contact->get('preferred_invoice') == 2): // if the customer prefers e-mail ?>

    <?php

    if ($kernel->user->hasModuleAccess('administration')) {
        $module_administration = $kernel->useModule('administration');
    }
    $valid_sender = true;

    switch($kernel->setting->get('intranet', 'debtor.sender')) {
        case 'intranet':
            if ($kernel->intranet->address->get('name') == '' || $kernel->intranet->address->get('email') == '') {
                $valid_sender = false;
                if ($kernel->user->hasModuleAccess('administration')) {
                    echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.$module_administration->getPath().'intranet_edit.php">'.$translation->get('do it now').'</a>.</p></div>';
                }
                else {
                    echo '<div class="message-dependent"><p>'.$translation->get('you need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';

                }
            }
            break;
        case 'user':
            if ($kernel->user->getAddress()->get('name') == '' || $kernel->user->getAddress()->get('email') == '') {
                $valid_sender = false;
                echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.url('/main/controlpanel/user_edit.php').'">'.$translation->get('do it now').'</a>.</p></div>';
            }
            break;
        case 'defined':
            if ($kernel->setting->get('intranet', 'debtor.sender.name') == '' || $kernel->setting->get('intranet', 'debtor.sender.email') == '') {
                $valid_sender = false;
                if ($kernel->user->hasModuleAccess('administration')) {
                    echo '<div class="message-dependent"><p>'.$translation->get('you need to fill in an e-mail address to send e-mail').'. <a href="'.$module_debtor->getPath().'settings.php">'.$translation->get('do it now').'</a>.</p></div>';
                }
                else {
                    echo '<div class="message-dependent"><p>'.$translation->get('you need to ask your administrator to fill in an e-mail address, so that you can send emails').'</p></div>';
                }

            }
            break;
        default:
            $valid_sender = false;
            trigger_error("Invalid sender!", E_USER_ERROR);
            exit;

    }

    if ($debtor->contact->address->get('email') == '') {
        $valid_sender = false;
        echo '<div class="message-dependent"><p>'.$translation->get('you need to register an e-mail to the contact, so you can send e-mails').'</p></div>';

    }
    ?>
<?php elseif ($debtor->contact->get('preferred_invoice') == 3): // electronic email, we make check that everything is as it should be ?>
    <?php

    if ($debtor->contact->address->get('ean') == '') {
        echo '<div class="message-dependent"><p>'.$translation->get('to be able to send electronic e-mails you need to fill out the EAN location number for the contact').'</p></div>';
    }

    $scan_in_contact_id = $kernel->setting->get('intranet', 'debtor.scan_in_contact');
    $valid_scan_in_contact = true;

    $scan_in_contact = new Contact($kernel, $scan_in_contact_id);
    if ($scan_in_contact->get('id') == 0) {
        $valid_scan_in_contact = false;
        echo '<div class="message-dependent"><p>';
        e($translation->get('a contact for the scan in bureau is needed to send electronic invoices').'. ');
        if ($kernel->user->hasModuleAccess('administration')) {
            echo '<a href="'.$debtor_module->getPath().'setting.php">'.$translation->get('do it now').'</a>.';
        }
        echo '</p></div>';

    } elseif (!$scan_in_contact->address->get('email')) {
        $valid_scan_in_contact = false;
        echo '<div class="message-dependent"><p>';
        e($translation->get('you need to provide a valid e-mail address to the contact for the scan in bureau').'.');
        echo ' <a href="'.$contact_module->getPath().'contact.php?id='.$scan_in_contact->get('id').'">'.$translation->get('do it now').'</a>.';
        echo '</p></div>';
    }
    ?>
<?php endif; ?>

<?php if (isset($email_send_with_success) && $email_send_with_success): ?>
    <div class="message-dependent"><p><?php e($translation->get('your email was sent').'.'); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>">
    <input type="hidden" name="id" value="<?php e($debtor->get('id')); ?>" />
    <?php if ($debtor->contact->get('preferred_invoice') == 2 AND  $debtor->get('status') == 'created' AND isset($valid_sender) AND $valid_sender == true): ?>
        <input type="submit" value="Send på e-mail" name="send_email" class="confirm" title="Dette vil sende e-mail til kontakten" />
    <?php elseif ($debtor->contact->get('preferred_invoice') == 2 AND $debtor->get('status') == 'sent' AND isset($valid_sender) AND $valid_sender == true): ?>
        <input type="submit" value="Genfremsend på e-mail" name="send_email" class="confirm" title="Dette vil sende fakturaen igen" />
    <?php elseif ($debtor->get("type") == 'invoice' AND $debtor->contact->get('preferred_invoice') == 3 AND $debtor->contact->address->get('ean') AND $debtor->get('status') == 'created' AND isset($valid_scan_in_contact) AND $valid_scan_in_contact == true): ?>
        <input type="submit" value="Send elektronisk faktura" name="send_electronic_invoice" class="confirm" title="Dette vil sende den elektroniske faktura til Læs-ind bureauet" />
    <?php elseif ($debtor->get("type") == 'invoice' AND $debtor->contact->get('preferred_invoice') == 3 AND $debtor->contact->address->get('ean') AND $debtor->get('status') == 'sent' AND isset($valid_scan_in_contact) AND $valid_scan_in_contact == true): ?>
        <input type="submit" value="Genfremsend elektronisk faktura" name="send_electronic_invoice" class="confirm" title="Dette vil sende den elektroniske faktura igen" />
    <?php endif; ?>
    <?php if ($debtor->get("status") == "created"): // make sure we can always mark as sent	?>
        <input type="submit" value="Marker som sendt" name="sent" />
    <?php endif; ?>

    <?php if (($debtor->get("type") == "invoice" && $debtor->get("status") == "created") || ($debtor->get("type") != "invoice" && $debtor->get("locked") == false)): ?>
        <input type="submit" value="Slet" class="confirm" title="Er du sikker på du vil slette denne <?php e(t($debtor->get('type').' title')); ?>?" name="delete" />
    <?php endif; ?>

    <?php if (($debtor->get("type") == "quotation" || $debtor->get("type") == "order") && ($debtor->get('status') == "created" || $debtor->get('status') == "sent")): ?>
        <input type="submit" value="Annuller" name="cancel" class="confirm" title="Er du sikker på, at du vil annullere?" />
    <?php endif; ?>

    <?php if ($debtor->get("type") == "quotation" && $debtor->get('status') == "sent" && $kernel->user->hasModuleAccess('order')): ?>
        <input type="submit" value="Læg ind som ordre" name="order" class="confirm" value="Er du sikker på, at du vil lægge tilbuddet ind som ordre?" />
    <?php endif; ?>
    <?php if ($debtor->get("type") == "quotation" && $debtor->get("status") == "sent" && $kernel->user->hasModuleAccess('invoice')): ?>
        <input type="submit" class="confirm" title="Er du sikker på, at du vil fakturere dette tilbud?" name="invoice" value="Fakturer tilbuddet" />
    <?php endif; ?>
    <?php if ($debtor->get("type") == "order" && $debtor->get("status") == "sent" && $kernel->user->hasModuleAccess('invoice')): ?>
        <input type="submit" class="confirm" title="Er du sikker på, at du vil fakturere denne ordre?" name="invoice" value="Fakturer ordre" />
    <?php endif; ?>
    <?php if ($debtor->get("type") == "invoice" && ($debtor->get("status") == "sent" OR $debtor->get("status") == 'executed')): // Opret kreditnota fra faktura ?>
        <input type="submit" class="confirm" title="Er du sikker på, at du vil kreditere denne faktura?" name="credit_note" value="Krediter faktura" />

    <?php endif; ?>

</form>

<?php /* ?>
    <?php if (count($debtor->contact->compare()) > 0 && $debtor->get('locked') == false) {	?>
        <div style="border: 2px orange solid; padding: 1.5em; margin: 1em 0;">
        <h2 style="margin-top: 0; border-left: 10px solid green; padding-left: 0.5em; font-size: 1em; font-weight: strong;">Kunden eksisterer måske allerede i databasen?</h2>
        <p>Kunden ligner nogle af de andre kunder i kundekartoteket (baseret på e-mail og postnummer). Du kan ændre kunde på ordren ved at vælge en i listen nedenunder.</p>
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
                foreach ($debtor->contact->compare() AS $value=>$key) {
                $contact = new Contact($kernel, $key);
                ?>
                <tr>
                    <td><?php e($contact->address->get('name')); ?></td>
                    <td><?php e($contact->address->get('address')); ?></td>
                    <td><?php e($contact->address->get('postcode')); ?> <?php e($contact->address->get('city')); ?></td>
                    <td><?php e($contact->address->get('phone')); ?></td>
                    <td><?php e($contact->address->get('email')); ?></td>
                    <td><a href="<?php e($_SERVER['PHP_SELF']); ?>?action=changecontact&amp;new_id=<?php e($contact->get('id')); ?>&amp;id=<?php e($debtor->get('id')); ?>" onclick="return confirm('Er du sikker på at du vil erstatte den nuværende kunde med den der er fundet i det eksisterende adressekartotek?');">[Vælg]</a></td>
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
        <caption><?php e(t($debtor->get('type').' title')); ?> information</caption>
        <tbody>
            <tr>
                <th>Dato</th>
                <td><?php e($debtor->get("dk_this_date")); ?></td>
            </tr>
            <?php if ($debtor->get("type") != "credit_note"): ?>
            <tr>
                <th><?php e(t($debtor->get('type').' due date')); ?>:</th>
                <td>
                    <?php e($debtor->get("dk_due_date")); ?>
                    <?php if ($debtor->get('type')=='invoice' && $debtor->anyDue($debtor->contact->get('id')) && $debtor->get("status") != 'executed') echo '<a href="reminder_edit.php?contact_id='.intval($debtor->contact->get('id')).'">Opret rykker</a>'; ?>
                </td>
            </tr>
            <?php endif; ?>



            <?php if ($kernel->setting->get('intranet', 'debtor.sender') == 'user' || $kernel->setting->get('intranet', 'debtor.sender') == 'defined'): ?>
                <tr>
                    <th>Vores ref.</th>
                        <td>
                            <?php
                            switch($kernel->setting->get('intranet', 'debtor.sender')) {
                                case 'user':
                                    e($kernel->user->getAddress()->get('name'). ' <'.$kernel->user->getAddress()->get('email').'>');
                                    break;
                                case 'defined':
                                    e($kernel->setting->get('intranet', 'debtor.sender.name').' <'.$kernel->setting->get('intranet', 'debtor.sender.email').'>');
                                    break;
                            }

                            if ($kernel->user->hasModuleAccess('administration')) { ?>
                                <a href="<?php e($debtor_module->getPath()); ?>setting.php" class="edit"><?php e($translation->get('change')); ?></a>
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
                        e(t($debtor->get("status")));

                    ?>
                </td>
            </tr>
            <?php if ($debtor->get("type") == "invoice" || $debtor->get("type") == "order") {	?>
                <tr>
                    <th><?php e(t('Payment method')); ?></th>
                    <td><?php e($debtor->get("translated_payment_method")); ?></td>
                </tr>
                <?php if ($debtor->get("payment_method") == 3) { ?>
                    <tr>
                        <th>Girolinje</th>
                        <td>+71&lt;<?php echo str_repeat("0", 15 - strlen($debtor->get("girocode"))).e($debtor->get("girocode")); ?> +<?php e($kernel->setting->get("intranet", "giro_account_number")); ?>&lt;</td>
                    </tr>
                <?php } ?>

                <?php if ($debtor->get("status") == "executed") { ?>
                    <tr>
                        <th><?php e(t('Date executed')); ?></th>
                        <td><?php e($debtor->get("dk_date_executed")); ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <th><?php e(t('Where from')); ?></th>
                <td>
                    <?php if (($debtor->get('where_from') == 'quotation' || $debtor->get('where_from') == 'order' || $debtor->get('where_from') == 'invoice') && $debtor->get("where_from_id") > 0) { ?>
                        <a href="view.php?id=<?php e($debtor->get("where_from_id")); ?>"><?php e(t($debtor->get("where_from"))); ?></a>
               <?php } else { ?>
                        <?php e(t($debtor->get('where_from'))); ?>
                    <?php } ?>
                </td>
            </tr>
            <?php if ($debtor->get('where_to') AND $debtor->get('where_to_id')): ?>
            <tr>
                <th><?php e(t('Where to')); ?></th>
                <td><a href="view.php?id=<?php e($debtor->get('where_to_id')); ?>"><?php e(t($debtor->get('where_to'))); ?></a></td>
            </tr>
            <?php endif; ?>
            <?php if (($debtor->get("type") == 'credit_note' || $debtor->get("type") == 'invoice') AND $kernel->user->hasModuleAccess('accounting')): ?>
            <tr>
                <th><?php e(t('Stated')); ?></th>
                <td>
                    <?php
                        if ($debtor->isStated()) {
                            $module_accounting = $kernel->useModule('accounting');
                            e($debtor->get('dk_date_stated'));
                            echo ' <a href="'.$module_accounting->getPath().'voucher.php?id='.$debtor->get('voucher_id').'">Se bilag</a>';
                        } else {
                            e(t('Not stated'));
                            if ($debtor->get('status') == 'sent' || $debtor->get('status') == 'executed') { ?>
                                <a href="state_<?php e($debtor->get('type')); ?>.php?id=<?php e($debtor->get("id")); ?>"><?php e($translation->get('state '.$debtor->get('type'))); ?></a>
                            <?php
                            }

                        }
                    ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($debtor->get("message") != ''): ?>
        <fieldset>
            <legend>Tekst</legend>
            <p><?php autohtml($debtor->get("message")); ?></p>
        </fieldset>
    <?php endif; ?>

    <?php if ($debtor->get("internal_note") != ''): ?>
        <fieldset>
            <legend><?php e(t('Internal note')); ?></legend>
            <?php
            $internal_note = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\" target=\"_blank\">\\0</a>", $debtor->get("internal_note"));
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
                <td><?php e($debtor->contact->get("number")); ?> <a href="view.php?id=<?php e($debtor->get('id')); ?>&amp;edit_contact=<?php e($debtor->contact->get('id')); ?>" class="edit">Ret</a></td>
            </tr>
            <tr>
                <th><?php e(t('Contact')); ?></th>
                <td><a href="<?php e($contact_module->getPath()); ?>contact.php?id=<?php e($debtor->contact->get('id')); ?>"><?php e($debtor->contact->address->get("name")); ?></a></td>
            </tr>
            <tr>
                <th><?php e(t('Address')); ?></th>
                <td class="adr">
                    <div class="adr">
                        <div class="street-address"><?php autohtml($debtor->contact->address->get("address")); ?></div>
                        <span class="postal-code"><?php e($debtor->contact->address->get('postcode')); ?></span>  <span class="location"><?php e($debtor->contact->address->get('city')); ?></span>
                        <div class="country"><?php e($debtor->contact->address->get('country')); ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php e(t('Email')); ?></th>
                <td><?php e($debtor->contact->address->get("email")); ?></td>
            </tr>
            <?php if ($debtor->contact->address->get("cvr") != '' && $debtor->contact->address->get("cvr") != 0): ?>
                <tr>
                    <th><?php e(t('CVR')); ?></th>
                    <td><?php e($debtor->contact->address->get("cvr")); ?></td>
                </tr>
            <?php endif; ?>

            <?php if (isset($debtor->contact_person) && strtolower(get_class($debtor->contact_person)) == "contactperson"): ?>
                <tr>
                    <th><?php e(t('Att.')); ?></th>
                    <td><?php e($debtor->contact_person->get("name")); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <?php if ($debtor->get("type") == "invoice" && $debtor->get("status") == "sent"):  ?>
        <div class="box">
            <h2><?php e(t('Register payment')); ?></h2>
            <form method="post" action="register_payment.php">
                <?php
                // @TODO: hack as long as the payment types are not the same as on the invoice
                if ($debtor->get('payment_method') == 2 || $debtor->get('payment_method') == 3) {
                    $payment_method = 1; // giro
                } elseif ($debtor->get('round_off')) {
                    $payment_method = 3; // cash
                } else {
                    $payment_method = 0; // bank_transfer
                }

                $payment = new Payment($debtor);
                $types = $payment->getTypes();
                ?>
                <input type="hidden" value="<?php e($debtor->get('id')); ?>" name="id" />
                <input type="hidden" value="invoice" name="for" />
                <input type="hidden" name="amount" value="<?php e(number_format($debtor->get("arrears"), 2, ",", ".")); ?>" />
                <input type="hidden" name="type" value="<?php e($payment_method); ?>" />

                <div>
                    <?php e(t('register')); ?> DKK <strong><?php e(number_format($debtor->get("arrears"), 2, ",", ".")); ?></strong> <?php e(t('paid by')); ?> <strong><?php e(t($types[$payment_method])); ?></strong>:
                </div>

                <div class="formrow">
                    <label for="payment_date" class="tight"><?php e(t('Date')); ?></label>
                    <input type="text" name="payment_date" id="payment_date" value="<?php e(date("d-m-Y")); ?>" size="8" />
                </div>

                <div style="clear: both;">
                    <input class="confirm" type="submit" name="payment" value="<?php e(t('Register')); ?>" title="<?php e(t('This will register the payment')); ?>" />
                    <?php e(t('or', 'common')); ?>
                    <a href="register_payment.php?for=invoice&amp;id=<?php e($debtor->get('id')); ?>"><?php e(t('give me more choices')); ?></a>.
                </div>
            </form>
            <p><a href="register_depreciation.php?for=invoice&amp;id=<?php e($debtor->get('id')); ?>"><?php e(t('I am not going to recieve the full payment...')); ?></a></p>
        </div>
    <?php endif; ?>

</div>

<div style="clear: both">

    <?php
    if ($debtor->get("type") == "invoice") {
        if ($kernel->user->hasModuleAccess('accounting')) {
            $module_accounting = $kernel->useModule('accounting');
        }

        $payments = $debtor->getDebtorAccount()->getList();
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
                            <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
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
                                    <a href="view.php?id=<?php e($payment["id"]); ?>"><?php e($payment["description"]); ?></a>
                                    <?php
                                } else {
                                    e($payment['description']);
                                }
                                ?>
                            </td>
                            <td class="amount"><?php e(number_format($payment["amount"], 2, ",", ".")); ?></td>
                            <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
                                <td>
                                    <?php if ($payment['is_stated']): ?>
                                        <a href="<?php e($module_accounting->getPath().'voucher.php?id='.$payment['voucher_id']); ?>"><?php e($translation->get('voucher')); ?></a>
                                    <?php elseif ($payment['type'] == 'credit_note'): ?>
                                        <a href="state_credit_note.php?id=<?php e($payment['id']) ?>"><?php e(t('state credit note')); ?></a>
                                    <?php elseif ($payment['type'] == 'depreciation'): ?>
                                        <a href="state_depreciation.php?for=invoice&amp;id=<?php e($debtor->get('id')); ?>&amp;depreciation_id=<?php e($payment['id']) ?>"><?php e(t('state depreciation')); ?></a>
                                    <?php else: ?>
                                        <a href="state_payment.php?for=invoice&amp;id=<?php e($debtor->get('id')); ?>&amp;payment_id=<?php e($payment['id']) ?>"><?php e(t('state payment')); ?></a>
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
                        <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
                            <td>&nbsp;</td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th>Manglende betaling</th>
                        <td class="amount"><?php e(number_format($debtor->get("total") - $payment_total, 2, ",", ".")); ?></td>
                        <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
                            <td>&nbsp;</td>
                        <?php endif; ?>
                    </tr>
                </table>
            <?php
        }
    }
    ?>

    <?php

    if (($debtor->get("type") == "order" || $debtor->get("type") == "invoice") && $kernel->intranet->hasModuleAccess('onlinepayment')) {

        $onlinepayment_module = $kernel->useModule('onlinepayment', true); // true: ignore user permisssion
        $onlinepayment = OnlinePayment::factory($kernel);
        $onlinepayment->getDBQuery()->setFilter('belong_to', $debtor->get("type"));
        $onlinepayment->getDBQuery()->setFilter('belong_to_id', $debtor->get('id'));
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
                        <?php foreach ($payment_list as $p): ?>
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
                                        (Ikke <acronym title="Betaling kan først hæves når faktura er sendt">hævet</acronym>)
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td class="amount">
                                    <?php
                                    if($p['currency'] && is_object($p['currency'])) {
                                        e($p['currency']->getType()->getIsoCode().' ');
                                    } elseif($kernel->intranet->hasModuleAccess('currency')) {
                                        e('DKK ');
                                    }
                                    e($p['dk_amount']);
                                    ?>
                                </td>
                                <td class="options">

                                    <?php if (count($actions) > 0 && $p['status'] == "authorized" && $kernel->user->hasModuleAccess('onlinepayment')): // Changed for better usability. $debtor->get("type") == "invoice" && $debtor->get("status") == "sent"    ?>
                                        <?php
                                        foreach ($actions AS $a) {
                                            if ($a['action'] != 'capture' || ($debtor->get("type") == "invoice" && $debtor->get("status") == "sent")) {
                                                ?>
                                                <a href="view.php?id=<?php e($debtor->get('id')); ?>&amp;onlinepayment_id=<?php e($p['id']); ?>&amp;onlinepayment_action=<?php e($a['action']); ?>" class="confirm"><?php e($a['label']); ?></a>
                                                <?php
                                            }
                                        }
                                        ?>
                                    <?php endif; ?>
                                    <?php if ($p['status'] == 'authorized'): ?>
                                        <a href="<?php e($onlinepayment_module->getPath()); ?>payment.php?id=<?php e($p['id']); ?>" class="edit"><?php e(t('edit payment')); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        // paymentmethodkey 5 is onlinepayment
        } elseif ($debtor->getPaymentMethodKey() == 5 AND $debtor->getWhereToId() == 0) {
            $payment_url = '<strong>Der findes ikke nogen url</strong>';
            try {
                $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($debtor->getWhereFromId());
                if ($shop) {
                    $payment_url = $debtor->getPaymentLink($shop->getPaymentUrl());
                }
            } catch (Doctrine_Record_Exeption $e) {
            }
            if ($shop AND $shop->getPaymentUrl()):
            ?>
            <div class="warning">
                Der burde være en onlinebetaling knyttet hertil. Måske har kunden fortrudt sit køb, eller også er der sket en fejl hos PBS under købet. Kunden kan betale på følgende link <?php e($payment_url); ?>. <a href="<?php e($_SERVER['PHP_SELF']); ?>?id=<?php e($debtor->getId()); ?>&amp;action=send_onlinepaymentlink">Skriv e-mail</a>.
            </div>
            <?php else: ?>
            <div class="warning">
                Der burde være en onlinebetaling knyttet hertil. Hvis du skriver et betalingslink ind under shoppen, kan du automatisk sende en e-mail til vedkommende.
            </div>

        <?php endif; }
    }
    ?>
<div style="clear:both;">
    <?php if ($debtor->get("locked") == false) { ?>
        <ul class="options" style="clear: both;">
            <li><a href="view.php?id=<?php e($debtor->get("id")); ?>&amp;add_item=true">Tilføj vare</a></li>
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
                <?php if ($kernel->intranet->hasModuleAccess('currency') && false !== $debtor->getCurrency()): ?>
                    <th><?php e($debtor->getCurrency()->getType()->getIsoCode()); ?></th>
                <?php endif; ?>
                <th>&nbsp;</th>
            </tr>
        </thead>


        <tbody>
            <?php
            $debtor->loadItem();
            $items = $debtor->item->getList();
            $total = 0;
            $total_currency = 0;
            if (isset($items[0]["vat"])) {
                $vat = $items[0]["vat"]; // Er der moms på det første produkt
            } else {
                $vat = 0;
            }

            for ($i = 0, $max = count($items); $i<$max; $i++) {
                $total += $items[$i]["quantity"] * $items[$i]["price"]->getAsIso(2);
                $vat = $items[$i]["vat"];
                ?>
                <tr id="i<?php e($items[$i]["id"]); ?>" <?php if (isset($_GET['item_id']) && $_GET['item_id'] == $items[$i]['id']) print(' class="fade"'); ?>>
                    <td><?php e($items[$i]["number"]); ?></td>
                    <td><?php e($items[$i]["name"]); ?>
                        <?php
                        if ($items[$i]["description"] != "") {
                            autohtml($items[$i]["description"]);
                            if ($debtor->get("locked") == false) {
                                echo '<br /> <a href="item_edit.php?debtor_id='.intval($debtor->get('id')).'&amp;id='.intval($items[$i]["id"]).'">Ret tekst</a>';
                            }
                        } elseif ($debtor->get("locked") == false) {
                            echo ' <a href="item_edit.php?debtor_id='.intval($debtor->get('id')).'&amp;id='.intval($items[$i]["id"]).'">Tilføj tekst</a>';
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
                    <?php if ($debtor->getCurrency()): ?>
                        <?php
                        $price_currency = new Ilib_Variable_Float($items[$i]["quantity"]*$items[$i]["price_currency"]->getAsIso(2));
                        $total_currency += $price_currency->getAsIso(2);
                        ?>
                        <td class="amount"><?php e($price_currency->getAsLocal('da_dk', 2)); ?></td>
                    <?php endif; ?>
                    <td class="options">
                        <?php
                        if ($debtor->get("locked") == false) {
                            ?>
                            <a class="moveup" href="view.php?id=<?php e($debtor->get("id")); ?>&amp;action=moveup&amp;item_id=<?php e($items[$i]["id"]); ?>"><?php e(t('Up')); ?></a>
                            <a class="movedown" href="view.php?id=<?php e($debtor->get("id")); ?>&amp;action=movedown&amp;item_id=<?php e($items[$i]["id"]); ?>"><?php e(t('Down')); ?></a>
                            <a class="edit" href="item_edit.php?debtor_id=<?php e($debtor->get('id')); ?>&amp;id=<?php e($items[$i]["id"]); ?>"><?php e(t('Edit')); ?></a>
                            <a class="delete" title="Dette vil slette varen!" href="view.php?id=<?php e($debtor->get("id")); ?>&amp;action=delete_item&amp;item_id=<?php e($items[$i]["id"]); ?>"><?php e(t('Delete')); ?></a>
                            <?php
                        }
                        ?>&nbsp;
                    </td>
                </tr>
                <?php

                if (($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
                    // Hvis der er moms på nuværende produkt, men næste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><b>25% moms af <?php e(number_format($total, 2, ",", ".")); ?></b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="amount"><b><?php e(number_format($total * 0.25, 2, ",", ".")); ?></b></td>
                        <?php if ($debtor->getCurrency()): ?>
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
        <?php if ($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) { ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">I alt:</td>
                <td class="amount"><?php e(number_format($total, 2, ",", ".")); ?></td>
                <?php if ($debtor->getCurrency()): ?>
                    <td class="amount"><?php e(number_format($total_currency, 2, ",", ".")); ?></td>
                <?php endif; ?>

                <td>&nbsp;</td>
            </tr>
            <?php } ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3"><b>Total<?php if ($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) e(" afrundet"); ?>:</b></td>
            <td class="amount"><strong><?php e(number_format($debtor->get("total"), 2, ",", ".")); ?></strong></td>
            <?php if ($debtor->getCurrency()): ?>
                <td class="amount"><strong><?php e(number_format($total_currency, 2, ",", ".")); ?></strong></td>
            <?php endif; ?>

            <td>&nbsp;</td>
        </tr>
    </table>
</div>
</div>

<?php
$page->end();
?>
