<?php
require '../../include_first.php';

$module = $kernel->module("procurement");
$procurement_object = new Procurement($kernel);
$translation = $kernel->getTranslation('procurement');

if (isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0 && $kernel->user->hasModuleAccess('contact')) {
    $contact_module = $kernel->useModule('contact');
    $contact = new Contact($kernel, $_GET['contact_id']);
    $procurement_object->dbquery->setFilter("contact_id", $_GET["contact_id"]);
}

if (isset($_GET["search"])) {

    if (isset($_GET["text"]) && $_GET["text"] != "") {
        $procurement_object->dbquery->setFilter("text", $_GET["text"]);
    }

    if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
        $procurement_object->dbquery->setFilter("from_date", $_GET["from_date"]);
    }

    if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
        $procurement_object->dbquery->setFilter("to_date", $_GET["to_date"]);
    }

    if (isset($_GET["status"])) {
        $procurement_object->dbquery->setFilter("status", $_GET["status"]);
    }
} else {

    if ($procurement_object->dbquery->checkFilter("contact_id")) {
      $procurement_object->dbquery->setFilter("status", "-1");
  } else {
        $procurement_object->dbquery->setFilter("status", "-2");
    }
}

$procurement_object->dbquery->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$procurement_object->dbquery->storeResult("use_stored", "procurement", "toplevel");
// $procurement_object->dbquery->setExtraUri('&amp;type='.$procurement_object->get("type"));


$procurements = $procurement_object->getList();

// $procurement_object->dbquery->setCondition("paid = 0 OR status = 0");

$page = new Intraface_Page($kernel);
$page->start('Indkøb');
?>

<h1>Indkøb<?php if (!empty($contact) AND is_object($contact) AND get_class($contact) == 'contact') e(": ".$contact->address->get('name')); ?></h1>

<ul class="options">
    <li><a href="edit.php">Opret nyt indkøb</a></li>

    <?php if (!empty($contact) AND is_object($contact) && get_class($contact) == 'contact'): ?>
    <li><a href="<?php e($contact_module->getPath()."contact.php?id=".$contact->get('id')); ?>">Gå til kontakten</a></li>
    <?php endif; ?>

</ul>

<?php if (!$procurement_object->isFilledIn()): ?>
    <p>Der er ikke oprettet nogen indkøb. <a href="edit.php">Opret et indkøb</a>.</p>
<?php else: ?>


<?php if (!isset($_GET['$contact_id'])): ?>
    <form method="get" action="index.php">
    <fieldset>
        <legend>Avanceret søgning</legend>
        <label>Tekst
            <input type="text" name="text" value="<?php e($procurement_object->dbquery->getFilter("text")); ?>" />
        </label>
        <label>Status
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($procurement_object->dbquery->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
            <option value="0"<?php if ($procurement_object->dbquery->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
            <option value="1"<?php if ($procurement_object->dbquery->getFilter("status") == 1) echo ' selected="selected"';?>>Modtaget</option>
            <option value="3"<?php if ($procurement_object->dbquery->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
        </select>
        </label>
        <label>Fra dato
            <input type="text" name="from_date" id="date-from" value="<?php e($procurement_object->dbquery->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label>Til dato
            <input type="text" name="to_date" value="<?php e($procurement_object->dbquery->getFilter("to_date")); ?>" />
        </label>
        <span>
        <input type="submit" name="search" value="Find" />
        </span>
    </fieldset>
    </form>
<?php endif; ?>


<table class="stripe">
    <caption>Indkøb</caption>
    <thead>
        <tr>
            <th>Nr.</th>
            <th>Fakturadato</th>
            <th>Beskrivelse</th>
            <th>Fra</th>
            <th>Leveringsdato</th>
            <th>Betalingsdato</th>
            <th>Pris</th>
        </tr>
    </thead>

    <tbody>
        <?php

        foreach ($procurements as $procurement) {
            ?>
            <tr>
                <td><?php e($procurement["number"]); ?></td>
                <td><?php e($procurement["dk_invoice_date"]); ?></td>


                <td><a href="view.php?id=<?php e($procurement["id"]); ?>"><?php e($procurement["description"]); ?></a></td>
                <td>
                    <?php
                    if ($kernel->user->hasModuleAccess('contact') && $procurement["contact_id"] != 0) {
                        $ModuleContact = $kernel->getModule('contact');
                        ?>
                        <a href="<?php e($ModuleContact->getPath()."contact.php?id=".$procurement["contact_id"]); ?>"><?php e($procurement["contact"]); ?>
                        <?php
                    } else {
                        e($procurement["vendor"]);
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($procurement["status"] == "recieved") {
                        e("Modtaget");
                    } elseif ($procurement["status"] == "canceled") {
                        e("Annulleret");
                    } elseif ($procurement["delivery_date"] != "0000-00-00") {
                        e($procurement["dk_delivery_date"]);
                    } else {
                        e("Ej oplyst");
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($procurement["status"] == "canceled") {
                        e("-");
                    } elseif ($procurement['paid_date'] != '0000-00-00') {
                        e('Betalt');
                    } elseif ($procurement["payment_date"] != "0000-00-00") {
                        e($procurement["dk_payment_date"]);
                    } else {
                        e("Ej oplyst");
                    }
                    ?>
                </td>
                <td>
                    <?php e(number_format($procurement["total_price"], 2, ',', '.')); ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php echo $procurement_object->dbquery->display('paging'); ?>

<?php endif; ?>
<?php
$page->end();
