<?php
require('../../include_first.php');

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');
$contact_module->includeFile('ContactReminder.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $contact = new Contact($kernel, $_POST['id']);

    if (!empty($_POST['send_email'])) {
        $contact->sendLoginEmail(Intraface_Mail::factory());
    } elseif (!empty($_POST['new_password'])) {
        if ($contact->generatePassword()) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $contact->getId());
            exit;
        }
    }

} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $contact = new Contact($kernel, (int)$_GET['id']);

    /*
    if (!empty($_GET['delete_msg']) AND is_numeric($_GET['delete_msg'])) {
        $contact->loadMessage($_GET['delete_msg']);
        $contact->message->delete();
    }
    else {
        $contact->loadMessage();
    }
    */
}

if ($kernel->user->hasModuleAccess('debtor')) {
    $debtor = $kernel->useModule('debtor');
    if ($kernel->user->hasModuleAccess("quotation")) {
        $quotation = new Debtor($kernel, 'quotation');
  }
    if ($kernel->user->hasModuleAccess('order')) {
        $kernel->useModule('order');
        $order = new Debtor($kernel, 'order');
    }
    if ($kernel->user->hasModuleAccess('invoice')) {
        $kernel->useModule('invoice');
        $invoice = new Invoice($kernel);
        $creditnote = new CreditNote($kernel);
        $reminder = new Reminder($kernel);
    }
}

// values
$value = $contact->get();
$address = $contact->address->get();
$delivery_address = $contact->delivery_address->get();

if ($value['type'] == "corporation") {
    $persons = $contact->contactperson->getList();
}

// The compare function has been removed from the class
// $similar_contacts = $contact->compare();
$similar_contacts = array();

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'viewcontact.js');
$page->start(__('contact information') . ' ' .$contact->get('name'));
?>


<div id="colOne">

<div class="box">

    <h1>#<?php e($value['number']); ?> <?php e($address['name']); ?></h1>

    <?php echo $contact->error->view(); ?>

    <ul class="options">
        <li><a href="contact_edit.php?id=<?php e($contact->get("id")); ?>"><?php e(t('edit', 'common')); ?></a></li>
        <li><a href="./?from_contact_id=<?php e($contact->get("id")); ?>&amp;use_stored=true"><?php e(t('close', 'common')); ?></a></li>
        <li><a class="vcard" href="vcard.php?id=<?php e($contact->get("id")); ?>"><?php e(t('vcard')); ?></a></li>
    </ul>

    <?php if ($kernel->user->hasModuleAccess("debtor")): ?>
        <ul class="options">
        <?php if ($kernel->user->hasModuleAccess("quotation")): ?>
            <?php if ($quotation->any('contact', $contact->get("id"))): ?>
            <li><a href="<?php e($debtor->getPath()); ?>list.php?type=quotation&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('quotation', 'debtor')); ?></a></li>
            <?php else: ?>
            <li class="inactive"><a href="<?php e($debtor->getPath()); ?>edit.php?type=quotation&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('create quotation', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($kernel->user->hasModuleAccess("order")): ?>
            <?php if ($order->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php e($debtor->getPath()); ?>list.php?type=order&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('orders', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php e($debtor->getPath()); ?>edit.php?type=order&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('create order', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($kernel->user->hasModuleAccess("invoice")): ?>
            <?php if ($invoice->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php e($debtor->getPath()); ?>list.php?type=invoice&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('invoices', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php e($debtor->getPath()); ?>edit.php?type=invoice&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('create invoice', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if ($creditnote->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php e($debtor->getPath()); ?>list.php?type=credit_note&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(t('credit notes', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if ($reminder->any($contact->get("id"))): ?>
                <li><a href="<?php e($debtor->getPath()); ?>reminders.php?contact_id=<?php e($contact->get("id")); ?>"><?php e(t('reminders', 'debtor')); ?></a></li>
            <?php elseif ($invoice->anyDue($contact->get("id"))): ?>
                <li class="inactive"><a href="<?php e($debtor->getPath()); ?>reminder_edit.php?contact_id=<?php e($contact->get("id")); ?>"><?php e(t('create reminder', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($kernel->user->hasModuleAccess("procurement")): ?>
            <?php
            $procurement_module = $kernel->useModule('procurement');
            $procurement = new Procurement($kernel);
            if ($procurement->any($contact->get('id'))) {
                ?>
                <li><a href="<?php e($procurement_module->getPath()."index.php?contact_id=".$contact->get('id')); ?>"><?php e(t('procurement', 'procurement')); ?></a></li>
                <?php
            }
            ?>
        <?php endif; ?>

        </ul>
    <?php endif; ?>
</div>

<?php /* Put in next version if (!empty($similar_contacts) AND is_array($similar_contacts) AND count($similar_contacts) > 0): ?>

    <p class="message">Der er kontakter, der ligner denne kontakt. <a href="contact_merge.php?id=<?php e($contact->get('id')); ?>">Videre</a></p>

<?php endif; */?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php e($contact->get('id')); ?>" />
    <table>
        <caption><?php e(t('contact information')); ?></caption>
        <tbody>
            <tr class="vcard">
                <th><?php e(t('address', 'address')); ?></th>
                <td class="adr">
                    <span class="fn"><?php e($address['name']); ?></span>
                    <div class="adr">
                        <div class="street-address"><?php autohtml($address['address']); ?></div>
                        <span class="postal-code"><?php e($address['postcode']); ?></span>
                        <span class="locality"><?php e($address['city']); ?></span>
                        <div class="country"><?php e($address['country']); ?></div>
                    </div>
                </td>
            </tr>
            <?php if (is_object($contact->delivery_address) AND !empty($delivery_address['address'])): ?>
            <tr class="vcard">
                <th><?php e(t('delivery address')); ?></th>
                <td>
                <span class="fn"><?php e($delivery_address['name']); ?></span>
                <div class="adr">
                    <div class="street-address"><?php autohtml($delivery_address['address']); ?></div>
                    <span class="postal-code"><?php e($delivery_address['postcode']); ?></span>
                    <span class="locality"><?php e($delivery_address['city']); ?></span>
                    <div class="country"><?php e($delivery_address['country']); ?></div>
                </div>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php e(t('phone', 'address')); ?></th>
                <td class="tel"><?php e($address['phone']); ?></td>
            </tr>
            <tr>
                <th><?php e(t('email', 'address')); ?></th>
                <td class="email"><?php e($address['email']); ?></td>
            </tr>
            <tr>
                <th><?php e(t('website', 'address')); ?></th>
                <td class="url"><?php e($address['website']); ?></td>
            </tr>
            <?php if ($kernel->intranet->get('identifier')): ?>
            <tr>
                <th><?php e(t('code')); ?></th>
                <td>
                    <?php e($contact->get('code')); ?>
                    <input type="submit" value="<?php e(t('new', 'common')); ?>" class="confirm" name="new_password" />
                    <?php if (!empty($address['email'])): ?>
                        <input type="submit" name="send_email" value="<?php e(t('send e-mail with login')); ?>" class="confirm" title="Er du sikker på, at du vil sende e-mail?" />
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </form>




<?php if ($value['type'] == "corporation"): ?>

  <?php if (count($persons) > 0) { ?>

    <table>
    <caption><?php e(t('contact persons')); ?></caption>
    <tbody>
    <?php
    foreach ($persons AS $person) { ?>
        <tr class="vcard">
        <td class="fn"><a href="contactperson_edit.php?contact_id=<?php e($contact->get('id') . '&id=' . $person['id']); ?>"><?php e($person['name']); ?></a></td>
        <td class="email"><?php e($person['email']); ?></td>
        <td class="tel"><?php e($person['phone']); ?></td>
        <td class="tel"><?php e($person['mobile']); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
    </table>
  <?php } ?>
    <ul class="options">
        <li><a href="contactperson_edit.php?contact_id=<?php e($value['id']); ?>"><?php e(t('add contact person')); ?></a></li>
    </ul>
<?php endif; ?>

<?php
$reminder = new ContactReminder($contact);
$reminders = $reminder->getList();
if (count($reminders) > 0):
    ?>
    <h2><?php e(t('reminders')); ?></h2>

    <table class="stripe">
        <caption><?php e(t('reminders')); ?></caption>
        <thead>
        <tr>
            <th><?php e(t('date')); ?></th>
            <th><?php e(t('subject')); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($reminders AS $reminder_item) {
            ?>
            <tr>
                <td class="date">
                    <?php
                    if (strtotime($reminder_item['reminder_date']) <= time()) { ?>
                        <span class="due"><?php e($reminder_item['dk_reminder_date']); ?></span>
                    <?php }
                    else {
                        e($reminder_item['dk_reminder_date']);
                    }
                    ?>
                </td>
                <td><a href="reminder.php?id=<?php e($reminder_item['id']); ?>"><?php e($reminder_item['subject']); ?></a></td>
                <td class="buttons"><a href="reminder_edit.php?id=<?php e($reminder_item['id']); ?>" class="edit"><?php e(t('edit', 'common')); ?></a></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <?php
endif;
?>

<ul class="options">
    <li><a href="reminder_edit.php?contact_id=<?php e($value['id']); ?>"><?php e(t('add reminder')); ?></a></li>
</ul>


</div>

<div id="colTwo">
<?php if ($value['type'] == "corporation"): ?>
<?php //if ($kernel->user->hasModuleAccess('debtor') AND !empty($address['cvr'])): ?>
<div id="paymentinformation" class="box">
<h2><?php e(t('payment information')); ?></h2>
<table class="stripe">
<caption><?php e(t('payment information')); ?></caption>
<?php if ($value['type'] != "private"): ?>

    <?php if (!empty($address['cvr'])): ?>
<tr>
    <td><?php e(t('cvr number', 'address')); ?></td>
    <td><a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php echo rawurlencode($address['cvr']); ?>"><?php e($address['cvr']); ?></a></td>
</tr>
    <?php endif; ?>
    <?php if (!empty($address['ean'])): ?>
<tr>
    <td><?php e(t('ean number', 'address')); ?></td>
    <td><?php e($address['ean']); ?></td>
</tr>
    <?php endif; ?>
<?php endif; ?>
<tr>
    <td><?php e(t('payment conditions')); ?></td>
     <td><?php
    foreach ($contact_module->getSetting("paymentcondition") AS $key=>$v) {
        if (isset($value['paymentcondition']) AND $v == $value['paymentcondition']) { e($v); }
    }
?> <?php e(t('days')); ?></td>
    </tr>
</table>
</div>
<?php endif; ?>

<div id="keywords" class="box <?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
 <h2><?php e(t('keywords', 'keyword')); ?></h2>
    <?php if ($contact->get('locked') == 0) { ?>
    <ul class="button"><li><a href="<?php e(url('/shared/keyword/connect.php', array('contact_id' => $contact->get('id')))); ?>"><?php e(t('add keywords', 'keyword')); ?></a></li></ul>
    <?php } ?>
    <?php
        $keyword = $contact->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) { ?>
            <ul id="keyword_list">
            <?php foreach ($keywords AS $k) { ?>
                <li><?php e($k['keyword']); ?></li>
            <?php } ?>
            </ul>
            <?php
        }
    ?>

</div>
<?php /*
<!-- Det her fjerner vi lige og så ser vi om nogen opdager det
<div id="messages" class="box">

<h2>Meddelelser</h2>

<ul class="button">
    <li><a href="message_edit.php?contact_id=<?php e($_GET['id']); ?>" id="createmessage">Opret meddelelse</a></li>
</ul>

<ol>
<?php foreach ($contact->message->getList() AS $m): ?>
    <li<?php if ($m['important'] == 1) echo ' style="color: blue"'; if (!empty($_GET['from_msg_id']) AND $_GET['from_msg_id']==$m['id']) echo ' id="message_'.$m['id'].'" class="fade"'; ?>>
      <?php echo nl2br($m['message']); ?>
      <a class="edit" href="message_edit.php?contact_id=<?php e($_GET['id']); ?>&amp;id=<?php e($m['id']);  ?>">Ret</a>
        <a href="contact.php?id=<?php e($_GET['id']); ?>&amp;delete_msg=<?php e($m['id']); ?>" class="delete" title="Er du sikker på, at du vil slette beskeden?">Slet</a>
    </li>
<?php endforeach; ?>
</ol>
</div>
*/ ?>

<?php if ($kernel->intranet->get('identifier')): ?>
<div class="box">
    <h2><?php e(t('tools')); ?></h2>
<ul>
    <li><a href="<?php e($contact->getLoginUrl()); ?>"><?php e(t('see contact login')); ?></a></li>
</ul>
</div>
<?php endif; ?>

</div>
<?php
$page->end();
?>
