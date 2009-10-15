<?php
require '../../include_first.php';

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

$redirect = Intraface_Redirect::factory($kernel, 'receive');


// prepare to save
if (!empty($_POST['eniro']) AND !empty($_POST['eniro_phone'])) {
    $contact = new Contact($kernel, $_POST['id']);

    $eniro = new Services_Eniro();
    $value = $_POST;

    if ($oplysninger = $eniro->query('telefon', $_POST['eniro_phone'])) {
        // skal kun bruges så længe vi ikke er utf8
        // $oplysninger = array_map('utf8_decode', $oplysninger);
        $address['name'] = $oplysninger['navn'];
        $address['address'] = $oplysninger['adresse'];
        $address['postcode'] = $oplysninger['postnr'];
        $address['city'] = $oplysninger['postby'];
        $address['phone'] = $_POST['eniro_phone'];
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // for a new contact we want to check if similar contacts alreade exists
    if (empty($_POST['id'])) {
        $contact = new Contact($kernel);
        if (!empty($_POST['phone'])) {
            $contact->getDBQuery()->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
            $similar_contacts = $contact->getList();
        }

    } else {
        $contact = new Contact($kernel, $_POST['id']);
    }

    // checking if similiar contacts exists
    if (!empty($similar_contacts) AND count($similar_contacts) > 0 AND empty($_POST['force_save'])) {
    } elseif ($id = $contact->save($_POST)) {

        // $redirect->addQueryString('contact_id='.$id);
        if ($redirect->get('id') != 0) {
            $redirect->setParameter('contact_id', $id);
        }
        //$contact->lock->unlock_post($id);
        header('Location: ' . $redirect->getRedirect('contact.php?id='.$id));
        exit;
    }

    $value = $_POST;
    $address = $_POST;
    $delivery_address = array();
    $delivery_address['name'] = $_POST['delivery_name'];
    $delivery_address['address'] = $_POST['delivery_address'];
    $delivery_address['postcode'] = $_POST['delivery_postcode'];
    $delivery_address['city'] = $_POST['delivery_city'];
    $delivery_address['country'] = $_POST['delivery_country'];

} elseif (isset($_GET['id'])) {
    $contact = new Contact($kernel, (int)$_GET['id']);
    $value = $contact->get();
    $address = $contact->address->get();
    $delivery_address = $contact->delivery_address->get();
} else {
    $contact = new Contact($kernel);
    $value['number'] = $contact->getMaxNumber() + 1;
}

if ($contact->get('id') != 0) {
    // i do not believe this is needed... we try to remove it!
    // $redirect->addQueryString('contact_id='.$contact->get('id'));
}


$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'contact_edit.js');
$page->start('Rediger kontakt');
?>


<h1><?php e(t('Edit contact')); ?></h1>

<?php echo $contact->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<?php if (empty($value['id'])): ?>

    <fieldset>
        <legend><?php e(t('Find address at Eniro')); ?></legend>
        <label for="eniro_phone"><?php e(t('Phone')); ?>
            <input id="eniro_phone" name="eniro_phone" value="<?php if (!empty($_POST['eniro_phone'])) e($_POST['eniro_phone']); ?>" />
        </label>
        <input type="submit" name="eniro" value="<?php e(t('Find address')); ?>" />
    </fieldset>

<?php endif; ?>

<?php if (!empty($similar_contacts) AND count($similar_contacts) > 0): ?>

    <p class="warning">Vi har fundet kontakter, der ligner den kontakt, du er ved at gemme. Vælg en af dem, hvis kontakten er den samme.</p>

    <table>
        <caption>Kontakter der ligner denne kontakt - baseret på telefonnummeret</caption>
        <thead>
        <tr>
            <th>Navn</th>
            <th>Adresse</th>
            <th>Postnr. og by</th>
            <th>Telefon</th>
            <th>E-mail</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($similar_contacts AS $c): ?>
            <tr>
                <td><?php e($c['name']); ?></td>
                <td><?php e($c['address']); ?></td>
                <td><?php e($c['postcode'] . ' ' . $c['city']); ?></td>
                <td><?php e($c['phone']); ?></td>
                <td><?php e($c['email']); ?></td>
                <td><a href="contact.php?id=<?php e($c['id']); ?>">Vælg</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p><input type="submit" name="force_save" value="Jeg vil gemme denne kontakt" /></p>

<?php endif; ?>

<fieldset>
    <legend><?php e(t('Contact information')); ?></legend>
    <input type="hidden" name="id" value="<?php if (!empty($value['id']))  e($value['id']); ?>" />

    <div class="formrow">
        <label for="number"><?php e(t('Contact number')); ?></label>
        <input type="text" name="number" id="number" value="<?php if (!empty($value['number'])) e($value['number']); ?>" />
    </div>
    <div class="formrow">
        <label for="name"><?php e(t('Name')); ?></label>
        <input type="text" name="name" id="name" value="<?php if (!empty($address['name'])) e($address['name']); ?>" size="30" />
    </div>
</fieldset>
<fieldset>
    <legend><?php e(t('Type')); ?></legend>
    <div class="formrow">
        <label for="contact-type"><?php e(t('Type')); ?></label>
        <select id="contact-type" name="type_key">
            <option value=""><?php e(t('Choose', 'common')); ?></option>
            <?php foreach ($contact_module->getSetting('type') AS $key=>$v): ?>
                <option value="<?php e($key); ?>"<?php if (isset($value['type_key']) AND $value['type_key'] == $key) { echo ' selected="selected"'; } ?>><?php e(__($v)); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>

<fieldset class="corporate" id="corporate">
    <legend><?php e(t('Information about company')); ?></legend>
    <div class="formrow">
        <label for="cvr"><acronym title="Centrale VirksomhedsRegister">CVR</acronym>-<?php e(t('number')); ?></label>
        <input type="text" name="cvr" id="cvr" value="<?php if (!empty($address['cvr'])) e($address['cvr']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('Address')); ?></legend>
    <div class="formrow">
        <label for="address"><?php e(t('Address')); ?></label>
        <textarea name="address" id="address" rows="2" cols="30"><?php if (!empty($address['address'])) e($address['address']); ?></textarea>
    </div>
    <div class="formrow">
        <label for="postalcode"><?php e(t('Zip')); ?></label>
        <input type="text" name="postcode" id="postalcode" value="<?php if (!empty($address['postcode']))  e($address['postcode']); ?>" />
    </div>
    <div class="formrow">
        <label for="town"><?php e(t('Town')); ?></label>
        <input type="text" name="city" id="town" value="<?php if (!empty($address['city']))  e($address['city']); ?>" />
    </div>
    <div class="formrow">
        <label for="country"><?php e(t('Country')); ?></label>
        <input type="text" name="country" id="country" value="<?php if (!empty($address['country']))  e($address['country']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('Contact information')); ?></legend>
    <div class="formrow">
        <label for="email"><?php e(t('Email')); ?></label>
        <input type="text" name="email" id="email" value="<?php  if (!empty($address['email'])) e($address['email']); ?>" />
    </div>
    <div class="formrow">
        <label for="phone"><?php e(t('Phone')); ?></label>
        <input type="text" name="phone" id="phone" value="<?php if (!empty($address['phone']))  e($address['phone']); ?>" />
    </div>
    <div class="fm-optional formrow">
        <label for="website"><?php e(t('Website')); ?></label>
        <input type="text" name="website" id="website" value="<?php if (!empty($address['website']))  e($address['website']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend><?php e(t('Delivery address')); ?></legend>
    <div class="formrow">
        <label for="deliveryname"><?php e(t('Name')); ?></label>
        <input type="text" name="delivery_name" id="deliveryname" value="<?php  if (!empty($delivery_address['name'])) e($delivery_address['name']); ?>" size="30" />
    </div>

    <div class="formrow">
        <label for="deliveryaddress"><?php e(t('Address')); ?></label>
        <textarea name="delivery_address" id="deliveryaddress" rows="2" cols="30"><?php  if (!empty($delivery_address['address'])) e($delivery_address['address']); ?></textarea>
    </div>
    <div class="formrow">
        <label for="deliverypostalcode"><?php e(t('Zip')); ?></label>
        <input type="text" name="delivery_postcode" id="deliverypostcode" value="<?php if (!empty($delivery_address['postcode'])) e($delivery_address['postcode']); ?>" />
    </div>
    <div class="formrow">
        <label for="deliverytown"><?php e(t('Town')); ?></label>
        <input type="text" name="delivery_city" id="deliverytown" value="<?php  if (!empty($delivery_address['city'])) e($delivery_address['city']); ?>" />
    </div>
    <div class="formrow">
        <label for="deliverycountry"><?php e(t('Country')); ?></label>
        <input type="text" name="delivery_country" id="deliverycountry" value="<?php  if (!empty($delivery_address['country'])) e($delivery_address['country']); ?>" />
    </div>
</fieldset>

<?php if ($kernel->user->hasModuleAccess('debtor')): ?>
<fieldset>
    <legend><?php e(t('Payment terms')); ?></legend>
    <div class="formrow">
        <label for="paymentcondition"><?php e(t('Days')); ?></label>
        <select name="paymentcondition" id="paymentcondition">

<?php foreach ($contact_module->getSetting("paymentcondition") AS $key=>$v) {

    echo "<option value=\"$v\"";
    if (isset($value['paymentcondition']) AND $v == $value['paymentcondition']) { echo ' selected="selected"'; }
    echo ">$v</option>";
}
?>
        </select> <?php e(t('days')); ?>
    </div>
</fieldset>

<?php if ($kernel->user->hasModuleAccess('invoice')): ?>
<fieldset>
    <legend><?php e(t('Invoice settings')); ?></legend>
    <div class="formrow">
        <label for="preferred_invoice"><?php e(t('Contact prefers')); ?></label>
        <select name="preferred_invoice" id="preferred-invoice">
            <option value="0"><?php e(t('Choose')); ?></option>
            <?php
                foreach ($contact_module->getSetting('preferred_invoice') AS $key=>$v) {
                    // skal ikke vise electronic ved privatperson
                    if (!empty($value['type']) AND $value['type'] == "private" AND $key == 3) continue;
                    ?>
                    <option value="<?php e($key); ?>"
                    <?php if (isset($value['preferred_invoice']) AND $key == $value['preferred_invoice']) { echo ' selected="selected"'; } ?>
                    ><?php e(__($v)); ?></option>
                <?php }
            ?>
        </select>
    </div>
</fieldset>
<fieldset id="invoice-electronic">
    <legend><?php e(t('Electronic invoice')); ?></legend>
    <div class="formrow">
        <label for="ean"><acronym title="En elektronisk postkasse">EAN</acronym>-<?php e(t('number')); ?></label>
        <input type="text" name="ean" id="ean" value="<?php if (!empty($address['ean'])) e($address['ean']); ?>" />
    </div>
</fieldset>
<?php endif; ?>
<?php endif; ?>

    <div>
        <input type="submit" name="submit" value="<?php e(t('Save', 'common')); ?>" id="save" class="save" />
        eller
        <?php
        if ($contact->get('id') != 0) {
            $url = 'contact.php?id='.$contact->get('id');
        }
        else {
            $url = 'index.php';
        }
        ?>
        <a href="<?php e($redirect->getCancelUrl($url)); ?>" title="Dette vil slette alle dine ændringer"><?php e(t('Cancel', 'common')); ?></a>
    </div>
</form>

<?php
$page->end();
?>
