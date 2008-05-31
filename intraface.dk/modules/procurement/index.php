<?php
require('../../include_first.php');

$module = $kernel->module("procurement");
$procurement = new Procurement($kernel);
$translation = $kernel->getTranslation('procurement');

if(isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0 && $kernel->user->hasModuleAccess('contact')) {
    $contact_module = $kernel->useModule('contact');
    $contact = new Contact($kernel, $_GET['contact_id']);
    $procurement->dbquery->setFilter("contact_id", $_GET["contact_id"]);
}

if(isset($_GET["search"])) {

    if(isset($_GET["text"]) && $_GET["text"] != "") {
        $procurement->dbquery->setFilter("text", $_GET["text"]);
    }

    if(isset($_GET["from_date"]) && $_GET["from_date"] != "") {
        $procurement->dbquery->setFilter("from_date", $_GET["from_date"]);
    }

    if(isset($_GET["to_date"]) && $_GET["to_date"] != "") {
        $procurement->dbquery->setFilter("to_date", $_GET["to_date"]);
    }

    if(isset($_GET["status"])) {
        $procurement->dbquery->setFilter("status", $_GET["status"]);
    }
}
else {

    if($procurement->dbquery->checkFilter("contact_id")) {
      $procurement->dbquery->setFilter("status", "-1");
  }
  else {
        $procurement->dbquery->setFilter("status", "-2");
    }
}

$procurement->dbquery->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$procurement->dbquery->storeResult("use_stored", "procurement", "toplevel");
// $procurement->dbquery->setExtraUri('&amp;type='.$procurement->get("type"));


$procurements = $procurement->getList();

// $procurement->dbquery->setCondition("paid = 0 OR status = 0");

$page = new Intraface_Page($kernel);
$page->start('Indkøb');
?>

<h1>Indkøb<?php if(!empty($contact) AND is_object($contact) AND get_class($contact) == 'contact') print(": ".$contact->address->get('name')); ?></h1>

<ul class="options">
    <li><a href="edit.php">Opret nyt indkøb</a></li>

    <?php if(!empty($contact) AND is_object($contact) && get_class($contact) == 'contact'): ?>
    <li><a href="<?php print($contact_module->getPath()."contact.php?id=".$contact->get('id')); ?>">Gå til kontakten</a></li>
    <?php endif; ?>

</ul>

<?php if (!$procurement->isFilledIn()): ?>
    <p>Der er ikke oprettet nogen indkøb. <a href="edit.php">Opret et indkøb</a>.</p>
<?php else: ?>


<?php if(!isset($_GET['$contact_id'])): ?>
    <form method="get" action="index.php">
    <fieldset>
        <legend>Avanceret søgning</legend>
        <label>Tekst
            <input type="text" name="text" value="<?php echo $procurement->dbquery->getFilter("text"); ?>" />
        </label>
        <label>Status
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($procurement->dbquery->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
            <option value="0"<?php if ($procurement->dbquery->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
            <option value="1"<?php if ($procurement->dbquery->getFilter("status") == 1) echo ' selected="selected"';?>>Modtaget</option>
            <option value="3"<?php if ($procurement->dbquery->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
        </select>
        </label>
        <label>Fra dato
            <input type="text" name="from_date" id="date-from" value="<?php print($procurement->dbquery->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label>Til dato
            <input type="text" name="to_date" value="<?php print($procurement->dbquery->getFilter("to_date")); ?>" />
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
            <th>Beskrivelse</th>
            <th>Fra</th>
            <th>Leveringsdato</th>
            <th>Betalingsdato</th>
            <th>Pris</th>
        </tr>
    </thead>

    <tbody>
        <?php

        for($i = 0, $max = count($procurements); $i < $max; $i++) {
            ?>
            <tr>
                <td><?php print($procurements[$i]["number"]); ?></td>
                <td><a href="view.php?id=<?php print($procurements[$i]["id"]); ?>"><?php print($procurements[$i]["description"]); ?></a></td>
                <td>
                    <?php
                    if($kernel->user->hasModuleAccess('contact') && $procurements[$i]["contact_id"] != 0) {
                        $ModuleContact = $kernel->getModule('contact');
                        ?>
                        <a href="<?php print($ModuleContact->getPath()."contact.php?id=".$procurements[$i]["contact_id"]); ?>"><?php print($procurements[$i]["contact"]); ?>
                        <?php
                    }
                    else {
                        print($procurements[$i]["vendor"]);
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($procurements[$i]["status"] == "recieved") {
                        print("Modtaget");
                    }
                    elseif($procurements[$i]["status"] == "canceled") {
                        print("Annulleret");
                    }
                    elseif($procurements[$i]["delivery_date"] != "0000-00-00") {
                        print($procurements[$i]["dk_delivery_date"]);
                    }
                    else {
                        print("Ej oplyst");
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($procurements[$i]["status"] == "canceled") {
                        print("-");
                    }
                    elseif($procurements[$i]['paid_date'] != '0000-00-00') {
                        print('Betalt');
                    }
                    elseif($procurements[$i]["payment_date"] != "0000-00-00") {
                        print($procurements[$i]["dk_payment_date"]);
                    }
                    else {
                        print("Ej oplyst");
                    }
                    ?>
                </td>
                <td>
                    <?php echo number_format($procurements[$i]["total_price"], 2, ',', '.'); ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php echo $procurement->dbquery->display('paging'); ?>

<?php endif; ?>
<?php

$page->end();
?>
