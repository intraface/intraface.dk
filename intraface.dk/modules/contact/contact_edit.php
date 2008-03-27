<?php
require('../../include_first.php');
require_once 'Services/Eniro.php';

$contact_module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

$redirect = Redirect::factory($kernel, 'receive');


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
            $contact->createDBQuery();
            $contact->dbquery->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
            $similar_contacts = $contact->getList();
        }

    } else {
        $contact = new Contact($kernel, $_POST['id']);
    }

    // checking if similiar contacts exists
    if (!empty($similar_contacts) AND count($similar_contacts) > 0 AND empty($_POST['force_save'])) {
    } elseif ($id = $contact->save($_POST)) {

        // $redirect->addQueryString('contact_id='.$id);
        if($redirect->get('id') != 0) {
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

if($contact->get('id') != 0) {
    // i do not believe this is needed... we try to remove it!
    // $redirect->addQueryString('contact_id='.$contact->get('id'));
}


$page = new Page($kernel);
$page->includeJavascript('module', 'contact_edit.js');
$page->start('Rediger kontakt');
?>


<h1>Rediger kontakt</h1>

<?php echo $contact->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

<?php if (empty($value['id'])): ?>

    <fieldset>
        <legend>Find adressen hos Eniro</legend>
        <label for="eniro_phone">Telefon
            <input id="eniro_phone" name="eniro_phone" value="<?php if (!empty($_POST['eniro_phone'])) echo $_POST['eniro_phone']; ?>" />
        </label>
        <input type="submit" name="eniro" value="Find adresseoplysninger" />
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
        <?php foreach($similar_contacts AS $c): ?>
            <tr>
                <td><?php echo safeToHtml($c['name']); ?></td>
                <td><?php echo safeToHtml($c['address']); ?></td>
                <td><?php echo safeToHtml($c['postcode']) . ' ' . safeToHtml($c['city']); ?></td>
                <td><?php echo safeToHtml($c['phone']); ?></td>
                <td><?php echo safeToHtml($c['email']); ?></td>
                <td><a href="contact.php?id=<?php echo intval($c['id']); ?>">Vælg</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p><input type="submit" name="force_save" value="Jeg vil gemme denne kontakt" /></p>

<?php endif; ?>

<fieldset>
    <legend>Kontaktoplysninger</legend>
    <input type="hidden" name="id" value="<?php if (!empty($value['id']))  echo intval($value['id']); ?>" />

    <div class="formrow">
        <label for="number">Kontaktnummer</label>
        <input type="text" name="number" id="number" value="<?php if (!empty($value['number'])) echo safeToForm($value['number']); ?>" />
    </div>
    <div class="formrow">
        <label for="name">Navn</label>
        <input type="text" name="name" id="name" value="<?php if (!empty($address['name'])) echo safeToForm($address['name']); ?>" size="30" />
    </div>
</fieldset>
<fieldset>
    <legend>Type</legend>
    <div class="formrow">
        <label for="contact-type">Type</label>
        <select id="contact-type" name="type_key">
            <option value="">Vælg</option>
            <?php foreach ($contact_module->getSetting('type') AS $key=>$v): ?>
                <option value="<?php echo $key; ?>"<?php if (isset($value['type_key']) AND $value['type_key'] == $key) { echo ' selected="selected"'; } ?>><?php echo safeToForm($translation->get($v)); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</fieldset>

<fieldset class="corporate" id="corporate">
    <legend>Oplysninger om firma</legend>
    <div class="formrow">
        <label for="cvr"><acronym title="Centrale VirksomhedsRegister">CVR</acronym>-nummer</label>
        <input type="text" name="cvr" id="cvr" value="<?php if (!empty($address['cvr'])) echo safeToForm($address['cvr']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend>Adresse</legend>
    <div class="formrow">
        <label for="address">Adresse</label>
        <textarea name="address" id="address" rows="2" cols="30"><?php if (!empty($address['address'])) echo safeToForm($address['address']); ?></textarea>
    </div>
    <div class="formrow">
        <label for="postalcode">Postnummer</label>
        <input type="text" name="postcode" id="postalcode" value="<?php if (!empty($address['postcode']))  echo safeToForm($address['postcode']); ?>" />
    </div>
    <div class="formrow">
        <label for="town">By</label>
        <input type="text" name="city" id="town" value="<?php if (!empty($address['city']))  echo safeToForm($address['city']); ?>" />
    </div>
    <div class="formrow">
        <label for="country">Land</label>
        <input type="text" name="country" id="country" value="<?php if (!empty($address['country']))  echo safeToForm($address['country']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend>Kontaktinformation</legend>
    <div class="formrow">
        <label for="email">E-mail</label>
        <input type="text" name="email" id="email" value="<?php  if (!empty($address['email'])) echo safeToForm($address['email']); ?>" />
    </div>
    <div class="formrow">
        <label for="phone">Telefon</label>
        <input type="text" name="phone" id="phone" value="<?php if (!empty($address['phone']))  echo safeToForm($address['phone']); ?>" />
    </div>
    <div class="fm-optional formrow">
        <label for="website">Website</label>
        <input type="text" name="website" id="website" value="<?php if (!empty($address['website']))  echo safeToForm($address['website']); ?>" />
    </div>
</fieldset>

<fieldset>
    <legend>Leveringsadresse</legend>
    <div class="formrow">
        <label for="deliveryname">Navn</label>
        <input type="text" name="delivery_name" id="deliveryname" value="<?php  if (!empty($delivery_address['name'])) echo safeToForm($delivery_address['name']); ?>" size="30" />
    </div>

    <div class="formrow">
        <label for="deliveryaddress">Adresse</label>
        <textarea name="delivery_address" id="deliveryaddress" rows="2" cols="30"><?php  if (!empty($delivery_address['address'])) echo safeToForm($delivery_address['address']); ?></textarea>
    </div>
    <div class="formrow">
        <label for="deliverypostalcode">Postnummer</label>
        <input type="text" name="delivery_postcode" id="deliverypostcode" value="<?php if (!empty($delivery_address['postcode'])) echo safeToForm($delivery_address['postcode']); ?>" />
    </div>
    <div class="formrow">
        <label for="deliverytown">By</label>
        <input type="text" name="delivery_city" id="deliverytown" value="<?php  if (!empty($delivery_address['city'])) echo safeToForm($delivery_address['city']); ?>" />
    </div>
    <div class="formrow">
        <label for="deliverycountry">Land</label>
        <input type="text" name="delivery_country" id="deliverycountry" value="<?php  if (!empty($delivery_address['country'])) echo safeToForm($delivery_address['country']); ?>" />
    </div>
</fieldset>

<?php if ($kernel->user->hasModuleAccess('debtor')): ?>
<fieldset>
    <legend>Betalingsbetingelser</legend>
    <div class="formrow">
        <label for="paymentcondition">Antal dage</label>
        <select name="paymentcondition" id="paymentcondition">

<?php foreach ($contact_module->getSetting("paymentcondition") AS $key=>$v) {

    echo "<option value=\"$v\"";
    if (isset($value['paymentcondition']) AND $v == $value['paymentcondition']) { echo ' selected="selected"'; }
    echo ">$v</option>";
}
?>
        </select> dage
    </div>
</fieldset>

<?php if ($kernel->user->hasModuleAccess('invoice')): ?>
<fieldset>
    <legend>Fakturaindstillinger</legend>
    <div class="formrow">
        <label for="preferred_invoice">Kunden foretrækker</label>
        <select name="preferred_invoice" id="preferred-invoice">
            <option value="0">Vælg</option>
            <?php
                foreach ($contact_module->getSetting('preferred_invoice') AS $key=>$v) {
                    // skal ikke vise electronic ved privatperson
                    if (!empty($value['type']) AND $value['type'] == "private" AND $key == 3) continue;
                    echo '<option value="'.$key.'"';
                    if (isset($value['preferred_invoice']) AND $key == $value['preferred_invoice']) { echo ' selected="selected"'; }
                    echo '>'.safeToForm($translation->get($v)).'</option>';
                }
            ?>
        </select>
    </div>
</fieldset>
<fieldset id="invoice-electronic">
    <legend>Elektronisk faktura</legend>
    <div class="formrow">
        <label for="ean"><acronym title="En elektronisk postkasse">EAN</acronym>-nummer</label>
        <input type="text" name="ean" id="ean" value="<?php if (!empty($address['ean'])) echo safeToForm($address['ean']); ?>" />
    </div>
</fieldset>
<?php endif; ?>
<?php endif; ?>

    <div>
        <input type="submit" name="submit" value="Gem" id="save" class="save" />
        eller
        <?php
        if($contact->get('id') != 0) {
            $url = 'contact.php?id='.$contact->get('id');
        }
        else {
            $url = 'index.php';
        }
        ?>
        <a href="<?php echo $redirect->getRedirect($url); ?>" title="Dette vil slette alle dine ændringer">Fortryd</a>
    </div>
</form>

<?php
$page->end();
?>
