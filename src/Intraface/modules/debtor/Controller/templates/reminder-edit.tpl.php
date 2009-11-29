<?php
$reminder = $context->getReminder();
$title = $reminder->get('title');
$value = $reminder->get();
$contact = $context->getContact();
$kernel = $context->getKernel();
if ($value['number'] == 0) {
    $value['number'] = $reminder->getMaxNumber() + 1;
    $value['dk_this_date'] = date('d-m-Y');
    $value['dk_due_date'] = date('d-m-Y');
}
$checked_invoice = array();
?>

<h1><?php e(t('Edit reminder')); ?></h1>

<?php
echo $reminder->error->view("html");
?>

<form action="<?php e(url(null, array($context->subview()))); ?>" method="post">

<fieldset>
    <legend><?php e(__('Information about the reminder')); ?></legend>

    <div class="formrow">
        <label for="number"><?php e(__('Number')); ?></label>
    <input type="text" name="number" id="number" value="<?php e($value["number"]); ?>" />
    </div>


    <div class="formrow">
        <label for="description"><?php e(__('Description')); ?></label>
       <input type="text" name="description" value="<?php if (isset($value['description'])) e($value["description"]); ?>" size="60" />
    </div>

    <div class="formrow">
        <label for="date"><?php e(__('Date')); ?></label>
        <input class="input" name="this_date" id="this_date" value="<?php if (isset($value['dk_this_date'])) e($value["dk_this_date"]); ?>" size="10" />
    </div>
    <div class="formrow">
        <label for="due_date"><?php e(__('Due date')); ?></label>
        <input class="input" name="due_date" id="due_date" value="<?php if (isset($value['dk_due_date'])) e($value["dk_due_date"]); ?>" size="10" />
    </div>
    <div class="formrow">
        <label for="reminder_fee"><?php e(__('Reminder fee')); ?></label>
        <select id="reminder_fee" name="reminder_fee">
            <option value="0" <?php if (isset($value["reminder_fee"]) && $value["reminder_fee"] == 0) print("selected=\"selected\""); ?> >Ingen</option>
            <option value="50" <?php if (isset($value["reminder_fee"]) && $value["reminder_fee"] == 50) print("selected=\"selected\""); ?> >50 kr.</option>
            <option value="100" <?php if (isset($value["reminder_fee"]) && $value["reminder_fee"] == 100) print("selected=\"selected\""); ?> >100 kr.</option>
            <?php if (isset($value["reminder_fee"]) && !($value["reminder_fee"] == 0 || $value["reminder_fee"] == 50 || $value["reminder_fee"] == 100)): ?>
                <option value="<?php e($value["reminder_fee"]); ?>" selected="selected"><?php e($value["reminder_fee"]); ?></option>
            <?php endif; ?>
        </select>
    </div>

    <div class="formrow">
        <label for="text"><?php e(__('Text for the contact')); ?></label>
           <textarea name="text" id="text" style="width: 400px; height: 100px;"><?php if (isset($value['text'])) e($value["text"]); ?></textarea>
    </div>

</fieldset>

<fieldset>
    <legend><?php e(__('Customer information')); ?></legend>
    <div class="formrow">
        <label><?php e(__('Customer')); ?></label>
        <span><?php e($contact->address->get("name")); ?></span>
    </div>

    <?php
    if ($contact->get("type") == "corporation") {
        ?>
        <div class="formrow">
            <label for="contact_person_id">Att.</label>
            <select name="contact_person_id" id="contact_person_id">
                <option value="0"></option>
                <?php
                $persons = $contact->contactperson->getList();

                for ($i = 0, $max = count($persons); $i < $max; $i++) {
                    ?>
                    <option value="<?php e($persons[$i]["id"]); ?>" <?php if (isset($value["contact_person_id"]) && $value["contact_person_id"] == $persons[$i]["id"]) print('selected="selected"'); ?> ><?php e($persons[$i]["name"]); ?></option>
                    <?php
                }
                ?>
                <option value="-1"><?php e(__('Create new') . ' >>'); ?></option>
            </select>
        </div>

        <fieldset id="contactperson">
            <legend><?php e(__('New contact person')); ?></legend>
            <div class="formrow">
                <label for="contact_person_name"><?php e(__('Name')); ?></label>
                <input type="text" name="contact_person_name" value="" />
            </div>
            <div class="formrow">
                <label for="contact_person_email"><?php e(__('Email')); ?></label>
                <input type="text" name="contact_person_email" value="" />
            </div>
        </fieldset>

        <?php
    }
    ?>
</fieldset>

<fieldset>
    <legend><?php e(__('Content')); ?></legend>

    <table>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?php e(__('No.')); ?></th>
                <th><?php e(__('Description')); ?></th>
                <th><?php e(__('Due date')); ?></th>
                <th><?php e(__('Amount')); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5"><b><?php e(__('Invoices with no payments')); ?></b></td>
            </tr>
            <?php
            $invoice = new Invoice($kernel);
            $invoice->getDBQuery()->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW()"); // status: 1 = sent
            $invoice->getDBQuery()->setSorting('this_date');
            $invoices = $invoice->getList();
            $total = 0;
            for ($i = 0, $max = count($invoices); $i < $max; $i++) {
                $total += $invoices[$i]["arrears"];
                ?>
                <tr>
                    <td>
                    <input type="checkbox" name="checked_invoice[]" value="<?php e($invoices[$i]["id"]); ?>" <?php if (in_array($invoices[$i]["id"], $checked_invoice) === true || empty($_GET["id"])) print("checked=\"checked\""); ?> /></td>
                    <td class="number"><?php e($invoices[$i]["number"]); ?></td>
                    <td><?php e($invoices[$i]["description"]); ?></td>
                    <td class="date"><?php e($invoices[$i]["dk_due_date"]); ?></td>
                    <td class="amount"><?php e(number_format($invoices[$i]["arrears"], 2, ",",".")); ?></td>
                </tr>
                <?php
            }
      $reminder->getDBQuery()->setCondition("contact_id = ".$contact->get("id")." AND status = 1 AND due_date < NOW() AND reminder_fee > 0"); // status: 1 = sent
      $reminder->getDBQuery()->setSorting('this_date');

            $reminders = $reminder->getList();
        if (!empty($reminders)) {
       ?>
            <tr>
                <td colspan="5"><b><?php e(__('Reminders with no payments')); ?></b></td>
            </tr>
            <?php
              for ($i = 0, $max = count($reminders); $i < $max; $i++) {
                  $total += $reminders[$i]["reminder_fee"];
                  if ($reminder->get("id") == $reminders[$i]["id"]) {
                      // Hvis man retter en reminder skal den selv ikke med på listen over mulige remindere!
                      continue;
                  }
                  ?>
                  <tr>
                      <td>
                      <input type="checkbox" name="checked_reminder[]" value="<?php e($reminders[$i]["id"]); ?>" <?php if (array_search($reminders[$i]["id"], $checked_reminder) !== FALSE || empty($_GET["id"])) print("checked=\"checked\""); ?> /></td>
                      <td class="number"><?php e($reminders[$i]["number"]); ?></td>
                      <td><?php e($reminders[$i]["description"]); ?></td>
                      <td class="date"><?php e($reminders[$i]["dk_due_date"]); ?></td>
                      <td class="amount"><?php e(number_format($reminders[$i]["reminder_fee"], 2, ",",".")); ?></td>
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
                <td class="amount"><?php e(number_format($total, 2, ",",".")); ?></td>
            </tr>
        </tfoot>
    </table>
</fieldset>

<?php
if ($contact->address->get('email')) {
    $send_as = array('email', 'pdf');

    ?>
  <fieldset>
  <legend>Send som</legend>
  <div>
  <!--
    <input type="radio" name="send_as" id="send_as_email" value="email" <?php if (isset($value['send_as']) && $value['send_as'] == 'email') { echo ' checked="checked"'; } ?> />
      <label for="send_as_email">Email</label><br />
    <input type="radio" name="send_as" id="send_as_pdf" value="pdf" <?php if (isset($value['send_as']) && $value['send_as'] == 'pdf') { echo ' checked="checked"'; } ?> />
       <label for="send_as_pdf">PDF</label>
    -->
<?php
    if (!isset($value['send_as'])) {
        // 0 is not set and 2 is email
        if ($contact->get('preferred_invoice') == 0 OR $contact->get('preferred_invoice') == 2) {
            $value['send_as'] = 'email';
        }
    }
    foreach ($send_as as $as) {
?>
    <input type="radio" name="send_as" id="send_as_<?php e($as); ?>" value="<?php e($as); ?>" <?php if (isset($value['send_as']) && $value['send_as'] == $as) { echo ' checked="checked"'; } ?> />
    <label for="send_as_<?php e($as); ?>"><?php e($as); ?></label><br />

<?php
    }
    ?>
  </div>
  </fieldset>
    <?php
} else {
    ?>
    <input type="hidden" name="send_as" value="pdf" />
    <?php
}
?>


<fieldset>
    <legend><?php e(__('Payment information')); ?></legend>
    <p>Hvilke betalingsoplysninger skal vises på rykkeren</p>
    <div>
        <input class="input" id="none" type="radio" name="payment_method_key" value="0" <?php if (isset($value["payment_method_key"]) && $value["payment_method_key"] == 0) print("checked=\"CHECKED\""); ?> />
        <label for="none">Ingen</label>
    </div>
    <?php if ($kernel->setting->get('intranet', 'bank_account_number')): ?>
    <div>
        <input class="input" id="account" type="radio" name="payment_method_key" value="1" <?php if (isset($value["payment_method_key"]) && $value["payment_method_key"] == 1) print("checked=\"CHECKED\""); ?> />
        <label for="account">Kontooverførsel</label>
    </div>
    <?php endif; ?>
    <?php if ($kernel->setting->get('intranet', 'giro_account_number')): ?>
    <div>
        <input class="input" type="radio" id="giro01" name="payment_method_key" value="2" <?php if (isset($value["payment_method_key"]) && $value["payment_method_key"] == 2) print("checked=\"CHECKED\""); ?> />
        <label for="giro01">Girokort +01</label>
    </div>
    <div>
        <input class="input" id="giro71" type="radio" name="payment_method_key" value="3" <?php if (isset($value["payment_method_key"]) && $value["payment_method_key"] == 3) print("checked=\"CHECKED\""); ?> />
        <label for="giro71">Girokort +71</label> &lt;
        <label for="girocode" style="display: none;">Girokode</label> <input class="input" name="girocode" id="girocode" value="<?php if (isset($value["girocode"])) e($value["girocode"]); ?>" size="16" onfocus="if (document.getElementById) document.getElementById('giro71').checked = true;" /> + <?php e($kernel->setting->get("intranet", "giro_account_number")); ?>&lt;
    </div>
    <?php endif; ?>
</fieldset>

<input type="submit" name="submit" value="<?php e(__('Save')); ?>" class="save" />
<?php if ($reminder->get('id') > 0): ?>
<a href="<?php e(url()); ?>"><?php e(__('Cancel')); ?></a>
<?php else: ?>
<a href="<?php e(url('../../contact/' . $contact->get('id'))); ?>"><?php e(__('Cancel')); ?></a>
<?php endif; ?>

<input type="hidden" name="id" value="<?php e($reminder->get("id")); ?>" />
<input type="hidden" name="contact_id" value="<?php e($contact->get('id')); ?>" />

</form>