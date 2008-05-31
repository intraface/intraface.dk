<?php
require('../../include_first.php');

$module = $kernel->module("debtor");

$translation = $kernel->getTranslation('debtor');

$mainInvoice = $kernel->useModule("invoice");
$mainInvoice->includeFile("Reminder.php");
$mainInvoice->includeFile("ReminderItem.php");

$mainCustomer = $kernel->useModule("contact");
$mainProduct = $kernel->useModule("product");

$checked_invoice = array();
$checked_reminder = array();

if(!empty($_POST)) {

    $reminder = new Reminder($kernel, intval($_POST["id"]));

    if(isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
        $contact = new Contact($kernel, $_POST["contact_id"]);
        $contact_person = new ContactPerson($contact);
        $person["name"] = $_POST['contact_person_name'];
        $person["email"] = $_POST['contact_person_email'];
        $contact_person->update($person);
        $contact_person->load();
        $_POST["contact_person_id"] = $contact_person->get("id");
    }

    if($reminder->save($_POST)) {

        if ($_POST['send_as'] == 'email') {
            header("Location: reminder_email.php?id=".$reminder->get("id"));
            exit;
        }
        else {
            header("Location: reminder.php?id=".$reminder->get("id"));
            exit;
        }
    }
    else {
        if(intval($_POST["id"]) != 0) {
            $title = "Ret rykker";
        }
        else {
            $title = "Ny rykker";
        }

        $value = $_POST;

        $value["dk_this_date"] = $value["this_date"];
        $value["dk_due_date"] = $value["due_date"];

        $contact = new contact($kernel, $_POST["contact_id"]);

        if(isset($value["checked_invoice"]) && is_array($value["checked_invoice"])) {
            $checked_invoice = $value["checked_invoice"];
        }
        else {
            $checked_invoice = array();
        }

        if(isset($value["checked_reminder"]) && is_array($value["checked_reminder"])) {
            $checked_reminder = $value["checked_reminder"];
        }
        else {
            $checked_reminder = array();
        }
    }
}
elseif(isset($_GET["id"])) {
    $title = "Ret rykker";
    $reminder = new Reminder($kernel, intval($_GET["id"]));
  $value = $reminder->get();
    $contact = new Contact($kernel, $reminder->get('contact_id'));

    $reminder->loadItem();
    $invoices = $reminder->item->getList("invoice");
    $reminders = $reminder->item->getList("reminder");

    for($i = 0, $max = count($invoices); $i < $max; $i++) {
        $checked_invoice[] = $invoices[$i]["invoice_id"];
    }

    for($i = 0, $max = count($reminders); $i < $max; $i++) {
        $checked_reminder[] = $reminders[$i]["reminder_id"];
    }
}
else {
    $title = "Ny rykker";
    $reminder = new Reminder($kernel);
    $contact = new Contact($kernel, $_GET['contact_id']);

    $value["dk_this_date"] = date("d-m-Y");
    $value["dk_due_date"] = date("d-m-Y", time()+3*24*60*60);
    /*
    if($contact->address->get("name") != $contact->address->get("contactname")) {
        $value["attention_to"] = $contact->address->get("contactname");
    }
    */
    $value["text"] = $kernel->setting->get('intranet', 'reminder.first.text');
    $value["payment_method_key"] = 1;
    $value["number"] = $reminder->getMaxNumber();
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'edit.js');
$page->start($title);

?>
<h1><?php print(safeToHtml($title)); ?></h1>

<?php
echo $reminder->error->view("html");
?>

<form action="<?php echo safeToHtml($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
    <legend>Rykkerinformation</legend>

    <div class="formrow">
        <label for="number">Rykkernr.</label>
    <input type="text" name="number" id="number" value="<?php print(safeToForm($value["number"])); ?>" />
    </div>


    <div class="formrow">
        <label for="description">Beskrivelse</label>
       <input type="text" name="description" value="<?php if(isset($value['description'])) print(safeToForm($value["description"])); ?>" size="60" />
    </div>

    <div class="formrow">
        <label for="date">Dato</label>
        <input class="input" name="this_date" id="this_date" value="<?php if(isset($value['dk_this_date'])) print(safeToForm($value["dk_this_date"])); ?>" size="10" />
    </div>
    <div class="formrow">
        <label for="due_date">Forfaldsdato</label>
        <input class="input" name="due_date" id="due_date" value="<?php if(isset($value['dk_due_date'])) print(safeToForm($value["dk_due_date"])); ?>" size="10" />
    </div>
    <div class="formrow">
        <label for="reminder_fee">Rykkergebyr</label>
        <select id="reminder_fee" name="reminder_fee">
            <option value="0" <?php if(isset($value["reminder_fee"]) && $value["reminder_fee"] == 0) print("selected=\"selected\""); ?> >Ingen</option>
            <option value="50" <?php if(isset($value["reminder_fee"]) && $value["reminder_fee"] == 50) print("selected=\"selected\""); ?> >50 kr.</option>
            <option value="100" <?php if(isset($value["reminder_fee"]) && $value["reminder_fee"] == 100) print("selected=\"selected\""); ?> >100 kr.</option>
            <?php if(isset($value["reminder_fee"]) && !($value["reminder_fee"] == 0 || $value["reminder_fee"] == 50 || $value["reminder_fee"] == 100)) print("<option value=\"".safeToHtml($value["reminder_fee"])."\" selcted=\"selected\">".safeToHtml($value["reminder_fee"])."</option>"); ?>
        </select>
    </div>

    <div class="formrow">
        <label for="text">Tekst til modtager</label>
           <textarea name="text" id="text" style="width: 400px; height: 100px;"><?php if(isset($value['text'])) print(safeToForm($value["text"])); ?></textarea>
    </div>

</fieldset>

<fieldset>
    <legend>Kundeoplysninger</legend>
    <div class="formrow">
        <label>Kunde</label>
        <span><?php print(safeToHtml($contact->address->get("name"))); ?></span>
    </div>

    <?php
    if($contact->get("type") == "corporation") {
        ?>
        <div class="formrow">
            <label for="contact_person_id">Att.</label>
            <select name="contact_person_id" id="contact_person_id">
                <option value="0"></option>
                <?php
                $persons = $contact->contactperson->getList();

                for($i = 0, $max = count($persons); $i < $max; $i++) {
                    ?>
                    <option value="<?php print(intval($persons[$i]["id"])); ?>" <?php if(isset($value["contact_person_id"]) && $value["contact_person_id"] == $persons[$i]["id"]) print('selected="selected"'); ?> ><?php print(safeToHtml($persons[$i]["name"])); ?></option>
                    <?php
                }
                ?>
                <option value="-1">Opret ny >></option>
            </select>
        </div>

        <fieldset id="contactperson">
            <legend>Ny kontaktperson</legend>
            <div class="formrow">
                <label for="contact_person_name">Navn:</label>
                <input type="text" name="contact_person_name" value="" />
            </div>
            <div class="formrow">
                <label for="contact_person_email">E-mail:</label>
                <input type="text" name="contact_person_email" value="" />
            </div>
        </fieldset>

        <?php
    }
    ?>
</fieldset>

<fieldset>
    <legend>Indhold</legend>

    <table>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Nr.</th>
                <th>Beskrivelse</th>
                <th>Forfaldsdato</th>
                <th>Beløb</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5"><b>Ikke betalte fakturaer:</b></td>
            </tr>
            <?php
            $invoice = new Invoice($kernel);
      /*
            $invoice_listfilter = new Listfilter;
      $invoice_listfilter->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW()"); // status: 1 = sent
            $invoice_listfilter->setSorting("this_date");
            $invoices = $invoice->getList($invoice_listfilter);
            */
      $invoice->dbquery->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW()"); // status: 1 = sent
      $invoice->dbquery->setSorting('this_date');

      $invoices = $invoice->getList();

            $total = 0;
            for($i = 0, $max = count($invoices); $i < $max; $i++) {
                $total += $invoices[$i]["arrears"];
                ?>
                <tr>
                    <td>
                    <input type="checkbox" name="checked_invoice[]" value="<?php print(intval($invoices[$i]["id"])); ?>" <?php if(in_array($invoices[$i]["id"], $checked_invoice) === true || empty($_GET["id"])) print("checked=\"checked\""); ?> /></td>
                    <td class="number"><?php print(intval($invoices[$i]["number"])); ?></td>
                    <td><?php print(safeToHtml($invoices[$i]["description"])); ?></td>
                    <td class="date"><?php print(safeToHtml($invoices[$i]["dk_due_date"])); ?></td>
                    <td class="amount"><?php print(number_format($invoices[$i]["arrears"], 2, ",",".")); ?></td>
                </tr>
                <?php
            }
      /*
            $reminder_listfilter = new Listfilter;
            $reminder_listfilter->setCondition("id != ".$reminder->get("id"));
            $reminder_listfilter->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW() AND reminder_fee > 0"); // status: 1 = sent
            $reminder_listfilter->setSorting("this_date");
      */
      //$reminder->dbquery->setCondition("id != ".(int)$reminder->get("id"));
      $reminder->dbquery->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW() AND reminder_fee > 0"); // status: 1 = sent
      $reminder->dbquery->setSorting('this_date');

            $reminders = $reminder->getList();
        if (!empty($reminders)) {
       ?>
            <tr>
                <td colspan="5"><b>Ikke betalte rykkere:</b></td>
            </tr>
            <?php
              for($i = 0, $max = count($reminders); $i < $max; $i++) {
                  $total += $reminders[$i]["reminder_fee"];
                  if($reminder->get("id") == $reminders[$i]["id"]) {
                      // Hvis man retter en reminder skal den selv ikke med på listen over mulige remindere!
                      continue;
                  }
                  ?>
                  <tr>
                      <td>
                      <input type="checkbox" name="checked_reminder[]" value="<?php print(intval($reminders[$i]["id"])); ?>" <?php if(array_search($reminders[$i]["id"], $checked_reminder) !== FALSE || empty($_GET["id"])) print("checked=\"checked\""); ?> /></td>
                      <td class="number"><?php print(intval($reminders[$i]["number"])); ?></td>
                      <td><?php print(safeToHtml($reminders[$i]["description"])); ?></td>
                      <td class="date"><?php print(safeToHtml($reminders[$i]["dk_due_date"])); ?></td>
                      <td class="amount"><?php print(number_format($reminders[$i]["reminder_fee"], 2, ",",".")); ?></td>
                  </tr>
                  <?php
              }
      }
      ?>

        </tbody>
        <tfoot>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>Total:</td>
                <td class="amount"><?php print(number_format($total, 2, ",",".")); ?></td>
            </tr>
        </tfoot>
    </table>
</fieldset>

<?php
if($contact->address->get('email')) {
    $send_as = array('email', 'pdf');

    ?>
  <fieldset>
  <legend>Send som</legend>
  <div>
  <!--
    <input type="radio" name="send_as" id="send_as_email" value="email" <?php if(isset($value['send_as']) && $value['send_as'] == 'email') { echo ' checked="checked"'; } ?> />
      <label for="send_as_email">Email</label><br />
    <input type="radio" name="send_as" id="send_as_pdf" value="pdf" <?php if(isset($value['send_as']) && $value['send_as'] == 'pdf') { echo ' checked="checked"'; } ?> />
       <label for="send_as_pdf">PDF</label>
    -->
<?php
    if(!isset($value['send_as'])) {
        // 0 is not set and 2 is email
        if ($contact->get('preferred_invoice') == 0 OR $contact->get('preferred_invoice') == 2) {
            $value['send_as'] = 'email';
        }
    }
    foreach ($send_as as $as) {
?>
    <input type="radio" name="send_as" id="send_as_<?php echo $as; ?>" value="<?php echo $as; ?>" <?php if(isset($value['send_as']) && $value['send_as'] == $as) { echo ' checked="checked"'; } ?> />
    <label for="send_as_<?php echo $as; ?>"><?php echo $as; ?></label><br />

<?php
    }
    ?>
  </div>
  </fieldset>
    <?php
}
else {
    ?>
    <input type="hidden" name="send_as" value="pdf" />
    <?php
}
?>


<fieldset>
    <legend>Betalingsoplysninger</legend>
    <p>Hvilke betalingsoplysninger skal vises på rykkeren</p>
    <div>
        <input class="input" id="none" type="radio" name="payment_method_key" value="0" <?php if(isset($value["payment_method_key"]) && $value["payment_method_key"] == 0) print("checked=\"CHECKED\""); ?> />
        <label for="none">Ingen</label>
    </div>
    <?php if ($kernel->setting->get('intranet', 'bank_account_number')): ?>
    <div>
        <input class="input" id="account" type="radio" name="payment_method_key" value="1" <?php if(isset($value["payment_method_key"]) && $value["payment_method_key"] == 1) print("checked=\"CHECKED\""); ?> />
        <label for="account">Kontooverførsel</label>
    </div>
    <?php endif; ?>
    <?php if ($kernel->setting->get('intranet', 'giro_account_number')): ?>
    <div>
        <input class="input" type="radio" id="giro01" name="payment_method_key" value="2" <?php if(isset($value["payment_method_key"]) && $value["payment_method_key"] == 2) print("checked=\"CHECKED\""); ?> />
        <label for="giro01">Girokort +01</label>
    </div>
    <div>
        <input class="input" id="giro71" type="radio" name="payment_method_key" value="3" <?php if(isset($value["payment_method_key"]) && $value["payment_method_key"] == 3) print("checked=\"CHECKED\""); ?> />
        <label for="giro71">Girokort +71</label> &lt;
        <label for="girocode" style="display: none;">Girokode</label> <input class="input" name="girocode" id="girocode" value="<?php if(isset($value["girocode"])) print(safeToForm($value["girocode"])); ?>" size="16" onfocus="if(document.getElementById) document.getElementById('giro71').checked = true;" /> + <?php echo safeToHtml($kernel->setting->get("intranet", "giro_account_number")); ?>&lt;
    </div>
    <?php endif; ?>
</fieldset>

<input type="submit" name="submit" value="Gem" class="save" /> eller
<?php if ($reminder->get('id') > 0): ?>
<a href="reminder.php?id=<?php echo intval($reminder->get('id')); ?>">Fortryd</a>
<?php else: ?>
<a href="/modules/contact/contact.php?id=<?php echo intval($contact->get('id')); ?>">Fortryd</a>
<?php endif; ?>

<input type="hidden" name="id" value="<?php print(intval($reminder->get("id"))); ?>" />
<input type="hidden" name="contact_id" value="<?php print(intval($contact->get('id'))); ?>" />

</form>

<?php

$page->end();
?>