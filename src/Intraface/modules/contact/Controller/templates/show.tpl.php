<?php
$value = $context->getValues();
$address = $context->getAddressValues();
$delivery_address = $context->getDeliveryAddressValues();
?>
<div id="colOne">

<div class="box">
    <img style="float: right;" src="<?php e('http://www.gravatar.com/avatar/'.md5($address['email']).'?s=60&d='.NET_SCHEME . NET_HOST . url('/images/icons/gravatar.png')); ?>" height="60" width="60" />

    <h1>#<?php e($value['number']); ?> <?php e($address['name']); ?></h1>

    <ul class="options" style="clear: none;">
        <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
        <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
        <li><a class="vcard" href="<?php e(url(null . '.vcf')); ?>"><?php e(t('Vcard')); ?></a></li>
    </ul>

    <ul class="options" style="clear: none;">
    <?php foreach ($context->getDependencies() as $key => $dependency) : ?>
            <?php if ($dependency['gateway']->findCountByContactId($context->getContact()->get("id")) > 0) : ?>
            <li><a href="<?php e($dependency['url']); ?>"><?php e(t(ucfirst($dependency['label'] . 's'))); ?></a></li>
            <?php elseif (!empty($dependency['url_create'])) : ?>
            <li class="inactive"><a href="<?php e($dependency['url_create']); ?>"><?php e(t('Create') . ' ' . t($dependency['label'])); ?></a></li>
            <?php endif; ?>
    <?php endforeach; ?>
    </ul>

    <?php /*if ($context->getKernel()->user->hasModuleAccess("debtor")): ?>
        <ul class="options">
        <?php if ($context->getKernel()->user->hasModuleAccess("quotation")): ?>
            <?php if ($context->getQuotation()->any('contact', $context->getContact()->get("id"))): ?>
            <li><a href="<?php e(url('../../debtor/quotation', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('Quotation', 'debtor')); ?></a></li>
            <?php else: ?>
            <li class="inactive"><a href="<?php e(url('../../debtor/quotation/create', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('create quotation', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($context->getKernel()->user->hasModuleAccess("order")): ?>
            <?php if ($context->getOrder()->any('contact', $context->getContact()->get("id"))): ?>
                <li><a href="<?php e(url('../../debtor/order', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('orders', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php e(url('../../debtor/order/create', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('create order', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($context->getKernel()->user->hasModuleAccess("invoice")): ?>
            <?php if ($context->getInvoice()->any('contact', $context->getContact()->get("id"))): ?>
                <li><a href="<?php e(url('../../debtor/invoice/list', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('invoices', 'debtor')); ?></a></li>
            <?php else: ?>
                <li class="inactive"><a href="<?php e(url('../../debtor/invoice/create', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('create invoice', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if ($context->getCreditnote()->any('contact', $context->getContact()->get("id"))): ?>
                <li><a href="<?php e(url('../../debtor/credit_note/list', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('credit notes', 'debtor')); ?></a></li>
            <?php endif; ?>
            <?php if ($context->getReminder()->any($context->getContact()->get("id"))): ?>
                <li><a href="<?php e(url('../../debtor/reminders', array('contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('reminders', 'debtor')); ?></a></li>
            <?php elseif ($context->getInvoice()->anyDue($context->getContact()->get("id"))): ?>
                <li class="inactive"><a href="<?php e(url('../../debtor/reminders', array('create', 'contact_id' => $context->getContact()->get("id")))); ?>"><?php e(t('create reminder', 'debtor')); ?></a></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($context->getKernel()->user->hasModuleAccess("procurement")): ?>
            <?php
            $procurement_module = $context->getKernel()->useModule('procurement');
            $procurement = new Procurement($context->getKernel());
            if ($procurement->any($context->getContact()->get('id'))) {
                ?>
                <li><a href="<?php e($procurement_module->getPath()."?contact_id=".$context->getContact()->get('id')); ?>"><?php e(t('procurement', 'procurement')); ?></a></li>
                <?php
            }
            ?>
        <?php endif; ?>

        </ul>
    <?php endif; */ ?>
</div>

<?php echo $context->getContact()->error->view(); ?>

<?php if ($context->getContact()->hasSimilarContacts()) : ?>

    <p class="message">Der er kontakter, der ligner denne kontakt. <a href="<?php e(url('merge')); ?>">Videre</a></p>

<?php endif; ?>

    <form action="<?php e(url()); ?>" method="post">
    <input type="hidden" name="_method" value="put" />
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
            <?php if (is_object($context->getContact()->delivery_address) and !empty($delivery_address['address'])) : ?>
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
            <?php if ($context->getKernel()->intranet->get('identifier')) : ?>
            <tr>
                <th><?php e(t('code')); ?></th>
                <td>
                    <?php e($context->getContact()->get('code')); ?>
                    <input type="submit" value="<?php e(t('new')); ?>" class="confirm" name="new_password" />
                    <?php if (!empty($address['email'])) : ?>
                        <input type="submit" name="send_email" value="<?php e(t('send e-mail with login')); ?>" class="confirm" title="Er du sikker p�, at du vil sende e-mail?" />
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </form>




<?php if ($value['type'] == "corporation") : ?>

    <?php if (count($persons) > 0) { ?>

    <table>
    <caption><?php e(t('contact persons')); ?></caption>
    <tbody>
    <?php
    foreach ($persons as $person) { ?>
        <tr class="vcard">
        <td class="fn"><a href="<?php e(url('contactperson/' . $person['id'], array('edit'))); ?>"><?php e($person['name']); ?></a></td>
        <td class="email"><?php e($person['email']); ?></td>
        <td class="tel"><?php e($person['phone']); ?></td>
        <td class="tel"><?php e($person['mobile']); ?></td>
        </tr>
    <?php                                                                                                         }
    ?>
    </tbody>
    </table>
    <?php } ?>
    <ul class="options">
        <li><a href="<?php e(url('contactperson', array('create'))); ?>"><?php e(t('Add contact person')); ?></a></li>
    </ul>
<?php endif; ?>

<?php
$reminder = new ContactReminder($context->getContact());
$reminders = $reminder->getList();
if (count($reminders) > 0) :
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
        foreach ($reminders as $reminder_item) {
            ?>
            <tr>
                <td class="date">
                    <?php
                    if (strtotime($reminder_item['reminder_date']) <= time()) { ?>
                        <span class="due"><?php e($reminder_item['dk_reminder_date']); ?></span>
                    <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         } else {
                        e($reminder_item['dk_reminder_date']);
}
                    ?>
                </td>
                <td><a href="<?php e(url('memo/' . $reminder_item['id'])); ?>"><?php e($reminder_item['subject']); ?></a></td>
                <td class="buttons"><a href="<?php e(url('memo/' .$reminder_item['id'], array('edit'))); ?>" class="edit"><?php e(t('edit')); ?></a></td>
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
    <li><a href="<?php e(url('memo', array('create'))); ?>"><?php e(t('Add memo')); ?></a></li>
</ul>


</div>

<div id="colTwo">
<?php if ($value['type'] == "corporation") : ?>
<?php //if ($context->getKernel()->user->hasModuleAccess('debtor') AND !empty($address['cvr'])): ?>
<div id="paymentinformation" class="box">
<h2><?php e(t('payment information')); ?></h2>
<table class="stripe">
<caption><?php e(t('payment information')); ?></caption>
<?php if ($value['type'] != "private") : ?>

    <?php if (!empty($address['cvr'])) : ?>
<tr>
    <td><?php e(t('cvr number', 'address')); ?></td>
    <td><a href="http://www.cvr.dk/Site/Forms/PublicService/DisplayCompany.aspx?cvrnr=<?php echo rawurlencode($address['cvr']); ?>"><?php e($address['cvr']); ?></a></td>
</tr>
    <?php endif; ?>
    <?php if (!empty($address['ean'])) : ?>
<tr>
    <td><?php e(t('ean number', 'address')); ?></td>
    <td><?php e($address['ean']); ?></td>
</tr>
    <?php endif; ?>
<?php endif; ?>
<tr>
    <td><?php e(t('payment conditions')); ?></td>
     <td><?php
        foreach ($context->getContactModule()->getSetting("paymentcondition") as $key => $v) {
            if (isset($value['paymentcondition']) and $v == $value['paymentcondition']) {
                e($v);
            }
        }
?> <?php e(t('days')); ?></td>
    </tr>
</table>
</div>
<?php endif; ?>

<div id="keywords" class="box <?php if (!empty($_GET['from']) and $_GET['from'] == 'keywords') {
    echo ' fade';
} ?>">
 <h2><?php e(t('keywords', 'keyword')); ?></h2>
    <?php if ($context->getContact()->get('locked') == 0) { ?>
    <ul class="button"><li><a href="<?php e(url('keyword/connect')); ?>"><?php e(t('Add keywords', 'keyword')); ?></a></li></ul>
    <?php } ?>
    <?php
        $keyword = $context->getContact()->getKeywordAppender();
        $keywords = $keyword->getConnectedKeywords();
    if (is_array($keywords) and count($keywords) > 0) { ?>
            <ul id="keyword_list">
            <?php foreach ($keywords as $k) { ?>
                <li><?php e($k['keyword']); ?></li>
            <?php } ?>
            </ul>
            <?php
    }
    ?>

</div>
<?php /*
<!-- Det her fjerner vi lige og s� ser vi om nogen opdager det
<div id="messages" class="box">

<h2>Meddelelser</h2>

<ul class="button">
    <li><a href="message_edit.php?contact_id=<?php e($_GET['id']); ?>" id="createmessage">Opret meddelelse</a></li>
</ul>

<ol>
<?php foreach ($context->getContact()->message->getList() AS $m): ?>
    <li<?php if ($m['important'] == 1) echo ' style="color: blue"'; if (!empty($_GET['from_msg_id']) AND $_GET['from_msg_id']==$m['id']) echo ' id="message_'.$m['id'].'" class="fade"'; ?>>
      <?php echo nl2br($m['message']); ?>
      <a class="edit" href="message_edit.php?contact_id=<?php e($_GET['id']); ?>&amp;id=<?php e($m['id']);  ?>">Ret</a>
        <a href="contact.php?id=<?php e($_GET['id']); ?>&amp;delete_msg=<?php e($m['id']); ?>" class="delete" title="Er du sikker p�, at du vil slette beskeden?">Slet</a>
    </li>
<?php endforeach; ?>
</ol>
</div>
*/ ?>

<?php if ($context->getKernel()->intranet->get('identifier')) : ?>
<div class="box">
    <h2><?php e(t('tools')); ?></h2>
<ul>
    <li><a href="<?php e($context->getContact()->getLoginUrl()); ?>"><?php e(t('see contact login')); ?></a></li>
</ul>
</div>
<?php endif; ?>
</div>