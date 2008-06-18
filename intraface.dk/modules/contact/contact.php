<?php
require('../../include_first.php');

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');
$contact_module->includeFile('ContactReminder.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $contact = new Contact($kernel, $_POST['id']);

    if (!empty($_POST['send_email'])) {
        $contact->sendLoginEmail(Intraface_Mail::factory());
    }
    elseif (!empty($_POST['new_password'])) {
        $contact->generatePassword();
        $contact->load();
    }

}
else if ($_SERVER['REQUEST_METHOD'] == 'GET') {

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

if($kernel->user->hasModuleAccess('debtor')) {
    $debtor = $kernel->useModule('debtor');
    if($kernel->user->hasModuleAccess("quotation")) {
        $quotation = new Debtor($kernel, 'quotation');
  }
    if($kernel->user->hasModuleAccess('order')) {
        $kernel->useModule('order');
        $order = new Debtor($kernel, 'order');
    }
    if($kernel->user->hasModuleAccess('invoice')) {
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
$page->start(safeToHtml($translation->get('contact information') . ' ' .$contact->get('name')));
?>


<div id="colOne">

<div class="box">

    <h1>#<?php echo safeToHtmL($value['number']); ?> <?php echo safeToHtml($address['name']); ?></h1>

    <?php echo $contact->error->view(); ?>

    <ul class="options">
        <li><a href="contact_edit.php?id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></li>
        <li><a href="index.php?from_contact_id=<?php print($contact->get("id")); ?>&amp;use_stored=true"><?php echo safeToHtml($translation->get('close', 'common')); ?></a></li>
        <li><a class="vcard" href="vcard.php?id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('vcard')); ?></a></li>
    </ul>

    <?php if($kernel->user->hasModuleAccess("debtor")): ?>
        <ul class="options">
        <?php if($kernel->user->hasModuleAccess("quotation")): ?>
            <?php if ($quotation->any('contact', $contact->get("id"))): ?>
            <li><a href="<?php print($debtor->getPath()); ?>list.php?type=quotation&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('quotation', 'debtor')); ?></a></li>
            <?php else: ?>
            <li class="inactive"><a href="<?php print($debtor->getPath()); ?>edit.php?type=quotation&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('create quotation', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($kernel->user->hasModuleAccess("order")): ?>
            <?php if($order->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php print($debtor->getPath()); ?>list.php?type=order&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('orders', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php print($debtor->getPath()); ?>edit.php?type=order&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('create order', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($kernel->user->hasModuleAccess("invoice")): ?>
            <?php if ($invoice->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php print($debtor->getPath()); ?>list.php?type=invoice&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('invoices', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php print($debtor->getPath()); ?>edit.php?type=invoice&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('create invoice', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if ($creditnote->any('contact', $contact->get("id"))): ?>
                <li><a href="<?php print($debtor->getPath()); ?>list.php?type=credit_note&amp;contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('credit notes', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if($reminder->any($contact->get("id"))): ?>
                <li><a href="<?php print($debtor->getPath()); ?>reminders.php?contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('reminders', 'debtor')); ?></a></li>
            <?php elseif($invoice->anyDue($contact->get("id"))): ?>
                <li class="inactive"><a href="<?php print($debtor->getPath()); ?>reminder_edit.php?contact_id=<?php print($contact->get("id")); ?>"><?php echo safeToHtml($translation->get('create reminder', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($kernel->user->hasModuleAccess("procurement")): ?>
            <?php
            $procurement_module = $kernel->useModule('procurement');
            $procurement = new Procurement($kernel);
            if($procurement->any($contact->get('id'))) {
                ?>
                <li><a href="<?php print($procurement_module->getPath()."index.php?contact_id=".$contact->get('id')); ?>"><?php echo safeToHtml($translation->get('procurement', 'procurement')); ?></a></li>
                <?php
            }
            ?>
        <?php endif; ?>

        </ul>
    <?php endif; ?>
</div>

<?php /* Put in next version if (!empty($similar_contacts) AND is_array($similar_contacts) AND count($similar_contacts) > 0): ?>

    <p class="message">Der er kontakter, der ligner denne kontakt. <a href="contact_merge.php?id=<?php echo $contact->get('id'); ?>">Videre</a></p>

<?php endif; */?>

    <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" name="id" value="<?php echo $contact->get('id'); ?>" />
    <table>
        <caption><?php echo safeToHtml($translation->get('contact information')); ?></caption>
        <tbody>
            <tr class="vcard">
                <th><?php echo safeToHtml($translation->get('address', 'address')); ?></th>
                <td class="adr">
                    <span class="fn"><?php echo safeToHtml($address['name']); ?></span>
                    <div class="adr">
                        <div class="street-address"><?php echo nl2br(safeToHtml($address['address'])); ?></div>
                        <span class="postal-code"><?php echo safeToHtml($address['postcode']); ?></span>
                        <span class="locality"><?php echo safeToHtml($address['city']); ?></span>
                        <div class="country"><?php echo safeToHtml($address['country']); ?></div>
                    </div>
                </td>
            </tr>
            <?php if (is_object($contact->delivery_address) AND !empty($delivery_address['address'])): ?>
            <tr class="vcard">
                <th><?php echo safeToHtml($translation->get('delivery address')); ?></th>
                <td>
                <span class="fn"><?php echo safeToHtml($delivery_address['name']); ?></span>
                <div class="adr">
                    <div class="street-address"><?php echo nl2br(safeToHtmL($delivery_address['address'])); ?></div>
                    <span class="postal-code"><?php echo safeToHtml($delivery_address['postcode']); ?></span>
                    <span class="locality"><?php echo safeToHtml($delivery_address['city']); ?></span>
                    <div class="country"><?php echo safeToHtml($delivery_address['country']); ?></div>
                </div>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?php echo safeToHtml($translation->get('phone', 'address')); ?></th>
                <td class="tel"><?php echo safeToHtml($address['phone']); ?></td>
            </tr>
            <tr>
                <th><?php echo safeToHtml($translation->get('email', 'address')); ?></th>
                <td class="email"><?php echo safeToHtml($address['email']); ?></td>
            </tr>
            <tr>
                <th><?php echo safeToHtml($translation->get('website', 'address')); ?></th>
                <td class="url"><?php echo safeToHtml($address['website']); ?></td>
            </tr>
            <?php if ($kernel->intranet->get('identifier')): ?>
            <tr>
                <th><?php echo safeToHtml($translation->get('code')); ?></th>
                <td>
                    <?php echo safeToHtml($contact->get('password')); ?>
                    <input type="submit" value="<?php echo safeToHtml($translation->get('new', 'common')); ?>" class="confirm" name="new_password" />
                    <?php if (!empty($address['email'])): ?>
                        <input type="submit" name="send_email" value="<?php echo safeToHtml($translation->get('send e-mail with login')); ?>" class="confirm" title="Er du sikker på, at du vil sende e-mail?" />
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
    <caption><?php echo safeToHtml($translation->get('contact persons')); ?></caption>
    <tbody>
    <?php
    foreach ($persons AS $person) {
        echo '<tr class="vcard">';
        echo '<td class="fn"><a href="contactperson_edit.php?contact_id='.$contact->get('id').'&amp;id=' . $person['id'] . '">'.$person['name'].'</a></td>';
        echo '<td class="email">'.safeToHtml($person['email']).'</td>';
        echo '<td class="tel">'.safeToHtml($person['phone']).'</td>';
        echo '<td class="tel">'.safeToHtmL($person['mobile']).'</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
    </table>
  <?php } ?>
    <ul class="options">
        <li><a href="contactperson_edit.php?contact_id=<?php echo intval($value['id']); ?>"><?php echo safeToHtml($translation->get('add contact person')); ?></a></li>
    </ul>
<?php endif; ?>

<?php
$reminder = new ContactReminder($contact);
$reminder->createDbquery();
$reminders = $reminder->getList();
if(count($reminders) > 0) {
    ?>
    <h2><?php echo safeToHtml($translation->get('reminders')); ?></h2>

    <table class="stripe">
        <caption><?php echo safeToHtml($translation->get('reminders')); ?></caption>
        <thead>
        <tr>
            <th><?php echo safeToHtml($translation->get('date')); ?></th>
            <th><?php echo safeToHtml($translation->get('subject')); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($reminders AS $reminder_item) {
            ?>
            <tr>
                <td class="date">
                    <?php
                    if(strtotime($reminder_item['reminder_date']) <= time()) {
                        echo '<span class="due">'.safeToHtml($reminder_item['dk_reminder_date']).'</span>';
                    }
                    else {
                        echo safeToHtml($reminder_item['dk_reminder_date']);
                    }
                    ?>
                </td>
                <td><a href="reminder.php?id=<?php echo intval($reminder_item['id']); ?>"><?php echo safeToHtml($reminder_item['subject']); ?></a></td>
                <td class="buttons"><a href="reminder_edit.php?id=<?php echo intval($reminder_item['id']); ?>" class="edit"><?php echo safeToHtml($translation->get('edit', 'common')); ?></a></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table
    <?php
}
?>

<ul class="options">
    <li><a href="reminder_edit.php?contact_id=<?php echo intval($value['id']); ?>"><?php echo safeToHtml($translation->get('add reminder')); ?></a></li>
</ul>


</div>

<div id="colTwo">
<?php if ($value['type'] == "corporation"): ?>
<?php //if ($kernel->user->hasModuleAccess('debtor') AND !empty($address['cvr'])): ?>
<div id="paymentinformation" class="box">
<h2><?php echo safeToHtml($translation->get('payment information')); ?></h2>
<table class="stripe">
<caption><?php echo safeToHtml($translation->get('payment information')); ?></caption>
<?php if ($value['type'] != "private"): ?>

    <?php if (!empty($address['cvr'])): ?>
<tr>
    <td><?php echo safeToHtml($translation->get('cvr number', 'address')); ?></td>
    <td><?php echo '<a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr='.rawurlencode($address['cvr']).'">' . safeToHtml($address['cvr']); ?></a></td>
</tr>
    <?php endif; ?>
    <?php if (!empty($address['ean'])): ?>
<tr>
    <td><?php echo safeToHtml($translation->get('ean number', 'address')); ?></td>
    <td><?php echo safeToHtml($address['ean']); ?></td>
</tr>
    <?php endif; ?>
<?php endif; ?>
<tr>
    <td><?php echo safeToHtml($translation->get('payment conditions')); ?></td>
     <td><?php
    foreach ($contact_module->getSetting("paymentcondition") AS $key=>$v) {
        if (isset($value['paymentcondition']) AND $v == $value['paymentcondition']) { echo safeToHtml($v); }
    }
?> <?php echo safeToHtml($translation->get('days')); ?></td>
    </tr>
</table>
</div>
<?php endif; ?>

<div id="keywords" class="box <?php if (!empty($_GET['from']) AND $_GET['from'] == 'keywords') echo ' fade'; ?>">
 <h2><?php echo safeToHtml($translation->get('keywords', 'keyword')); ?></h2>
    <?php if ($contact->get('locked') == 0) { ?>
    <ul class="button"><li><a href="<?php echo PATH_WWW; ?>/shared/keyword/connect.php?contact_id=<?php echo $contact->get('id'); ?>"><?php echo safeToHtml($translation->get('add keywords', 'keyword')); ?></a></li></ul>
    <?php } ?>
    <?php
        $keyword = $contact->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
        if (is_array($keywords) AND count($keywords) > 0) {
            echo '<ul id="keyword_list">';
            foreach ($keywords AS $k) {
                echo '<li>' . safeToHtml($k['keyword']) . '</li>';
            }
            echo '</ul>';
        }
    ?>

</div>
<?php /*
<!-- Det her fjerner vi lige og så ser vi om nogen opdager det
<div id="messages" class="box">

<h2>Meddelelser</h2>

<ul class="button">
    <li><a href="message_edit.php?contact_id=<?php echo $_GET['id']; ?>" id="createmessage">Opret meddelelse</a></li>
</ul>

<ol>
<?php foreach ($contact->message->getList() AS $m): ?>
    <li<?php if ($m['important'] == 1) echo ' style="color: blue"'; if (!empty($_GET['from_msg_id']) AND $_GET['from_msg_id']==$m['id']) echo ' id="message_'.$m['id'].'" class="fade"'; ?>>
      <?php echo nl2br($m['message']); ?>
      <a class="edit" href="message_edit.php?contact_id=<?php echo $_GET['id']; ?>&amp;id=<?php echo $m['id'];  ?>">Ret</a>
        <a href="contact.php?id=<?php echo $_GET['id']; ?>&amp;delete_msg=<?php echo $m['id']; ?>" class="delete" title="Er du sikker på, at du vil slette beskeden?">Slet</a>
    </li>
<?php endforeach; ?>
</ol>
</div>
*/ ?>

<?php if ($kernel->intranet->get('identifier')): ?>
<div class="box">
    <h2><?php echo safeToHtml($translation->get('tools')); ?></h2>
<ul>
    <li><a href="<?php echo $contact->getLoginUrl(); ?>"><?php echo safeToHtml($translation->get('see contact login')); ?></a></li>
</ul>
</div>
<?php endif; ?>

</div>
<?php
$page->end();
?>
