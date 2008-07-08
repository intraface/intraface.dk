<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('debtor');

$mDebtor = $kernel->module('debtor');
$contact_module = $kernel->useModule('contact');
$product_module = $kernel->useModule('product');

if (empty($_GET['id'])) $_GET['id'] = '';
if (empty($_GET['type'])) $_GET['type'] = '';
if (empty($_GET["contact_id"])) $_GET['contact_id'] = '';
if (empty($_GET["status"])) $_GET['status'] = '';

$debtor = Debtor::factory($kernel, intval($_GET["id"]), $_GET["type"]);


if(isset($_GET["action"]) && $_GET["action"] == "delete") {
    // $debtor = new CreditNote($kernel, (int)$_GET["delete"]);
    $debtor->delete();
}

if(isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0) {
    $debtor->getDBQuery()->setFilter("contact_id", $_GET["contact_id"]);
}

if(isset($_GET["product_id"]) && intval($_GET["product_id"]) != 0) {
    $debtor->getDBQuery()->setFilter("product_id", $_GET["product_id"]);
}


// søgning
    // if(isset($_POST['submit'])
    if(isset($_GET["text"]) && $_GET["text"] != "") {
        $debtor->getDBQuery()->setFilter("text", $_GET["text"]);
    }

    if(isset($_GET["from_date"]) && $_GET["from_date"] != "") {
        $debtor->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
    }

    if(isset($_GET["to_date"]) && $_GET["to_date"] != "") {
        $debtor->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
    }

    if($debtor->getDBQuery()->checkFilter("contact_id")) {
        $debtor->getDBQuery()->setFilter("status", "-1");
    } elseif(isset($_GET["status"]) && $_GET['status'] != '') {
        $debtor->getDBQuery()->setFilter("status", $_GET["status"]);
    } else {
        $debtor->getDBQuery()->setFilter("status", "-2");
    }

    if(!empty($_GET['not_stated']) AND $_GET['not_stated'] == 'true') {
        $debtor->getDBQuery()->setFilter("not_stated", true);
    }

// er der ikke noget galt herunder (LO) - brude det ikke være order der bliver sat?
if(isset($_GET['sorting']) && $_GET['sorting'] != 0) {
    $debtor->getDBQuery()->setFilter("sorting", $_GET['sorting']);
}

$debtor->getDBQuery()->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$debtor->getDBQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");
$debtor->getDBQuery()->setExtraUri('&amp;type='.$debtor->get("type"));

$posts = $debtor->getList();

if(intval($debtor->getDBQuery()->getFilter('product_id')) != 0) {
    $product = new Product($kernel, $debtor->getDBQuery()->getFilter('product_id'));
}

if(intval($debtor->getDBQuery()->getFilter('contact_id')) != 0) {
    $contact = new Contact($kernel, $debtor->getDBQuery()->getFilter('contact_id'));
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'list.js');
$page->start(safeToHtml($translation->get($debtor->get('type').'s')));
?>

<h1>
    <?php
        print($translation->get($debtor->get("type").'s'));
        if (!empty($contact) AND is_object($contact) && $contact->address->get('name') != '') {
            echo ': ' . safeToHtml($contact->address->get('name'));
        }

        if (!empty($product) AND is_object($product) && $product->get('name') != '') {
            echo ' med produkt: ' . safeToHtml($product->get('name'));
        }
    ?>
</h1>

<?php if($kernel->intranet->address->get('id') == 0): ?>
    <p>Du mangler at udfylde adresse til dit intranet. Det skal du gøre, før du kan oprette en <?php print(safeToHtml(strtolower($translation->get($debtor->get('type'))))); ?>.
    <?php if($kernel->user->hasModuleAccess('administration')): ?>
        <?php
        $module_administration = $kernel->useModule('administration');
        ?>
        <a href="<?php echo safeToHtml($module_administration->getPath().'intranet_edit.php'); ?>">Udfyld adresse</a>.</p>
    <?php else: ?>
        Du har ikke adgang til at rette adresseoplysningerne, det må du bede din administrator om at gøre.</p>
    <?php endif; ?>

<?php elseif (!$debtor->isFilledIn()): ?>

    <?php if($debtor->get('type') == 'credit_note'): ?>
        <p>Du har endnu ikke oprettet nogen. Kreditnotaer oprettes fra en fakturaer.</p>
    <?php else: ?>
        <p>Du har endnu ikke oprettet nogen. <a href="select_contact.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a>.</p>
    <?php endif; ?>
<?php else: ?>

<ul class="options">
    <?php if(!empty($contact) AND is_object($contact) AND $debtor->get("type") != "credit_note"): ?>
        <li><a href="edit.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>&amp;contact_id=<?php print(intval($contact->get("id"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a></li>
        <li><a href="<?php echo $contact_module->getPath(); ?>contact.php?id=<?php echo intval($contact->get('id')); ?>">Vis kontakten</a>
    <?php else: ?>
        <?php if(!empty($_GET['product_id'])): ?>
            <li><a href="<?php echo $product_module->getPath(); ?>product.php?id=<?php echo intval($product->get('id')); ?>">Vis produktet</a>
        <?php endif; ?>
        <li><a href="select_contact.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a></li>
    <?php endif; ?>
    <li><a class="excel" href="export_excel.php?type=<?php print(safeToHtml($debtor->get('type'))); ?>&amp;use_stored=true">Exporter liste til Excel</a></li>
</ul>


<?php echo $debtor->error->view(); ?>

<?php if(!isset($_GET['$contact_id'])): ?>

    <fieldset class="hide_on_print">
        <legend>Avanceret søgning</legend>
        <form method="get" action="list.php">
        <label>Tekst
            <input type="text" name="text" value="<?php echo safeToHtml($debtor->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label>Status
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($debtor->getDBQuery()->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
            <?php if($debtor->get("type") == "invoice"): ?>
            <option value="-3"<?php if ($debtor->getDBQuery()->getFilter("status") == -3) echo ' selected="selected"';?>>Afskrevet</option>
            <?php endif; ?>
            <option value="0"<?php if ($debtor->getDBQuery()->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
            <option value="1"<?php if ($debtor->getDBQuery()->getFilter("status") == 1) echo ' selected="selected"';?>>Sendt</option>
            <option value="2"<?php if ($debtor->getDBQuery()->getFilter("status") == 2) echo ' selected="selected"';?>>Afsluttet</option>
            <option value="3"<?php if ($debtor->getDBQuery()->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
        </select>
        </label>
        <!-- sortering bør være placeret ved at man klikker på en overskrift i stedet - og så bør man kunne sortere på det hele -->
        <label>Sortering
        <select name="sorting">
            <option value="0"<?php if ($debtor->getDBQuery()->getFilter("sorting") == 0) echo ' selected="selected"';?>>Fakturanummer faldende</option>
            <option value="1"<?php if ($debtor->getDBQuery()->getFilter("sorting") == 1) echo ' selected="selected"';?>>Fakturanummer stigende</option>
            <option value="2"<?php if ($debtor->getDBQuery()->getFilter("sorting") == 2) echo ' selected="selected"';?>>Kontaktnummer</option>
            <option value="3"<?php if ($debtor->getDBQuery()->getFilter("sorting") == 3) echo ' selected="selected"';?>>Kontaktnavn</option>
        </select>
        </label>
        <br />

        <label>Fra dato
            <input type="text" name="from_date" id="date-from" value="<?php print(safeToForm($debtor->getDBQuery()->getFilter("from_date"))); ?>" /> <span id="calender"></span>
        </label>
        <label>Til dato
            <input type="text" name="to_date" value="<?php print(safeToForm($debtor->getDBQuery()->getFilter("to_date"))); ?>" />
        </label>

        <span>
        <input type="hidden" name="type" value="<?php print(safeToForm($debtor->get("type"))); ?>" />
        <input type="hidden" name="contact_id" value="<?php print(intval($debtor->getDBQuery()->getFilter('contact_id'))); ?>" />
        <input type="hidden" name="product_id" value="<?php print(intval($debtor->getDBQuery()->getFilter('product_id'))); ?>" />
        <input type="submit" name="search" value="Find" />
        </span>



        </form>
    </fieldset>

<?php endif; ?>




<table class="stripe">
    <caption><?php echo safeToHtml($translation->get($debtor->get("type").' title')); ?></caption>
    <thead>
        <tr>
            <th>Nr.</th>
            <th colspan="2">Kontakt</th>
            <th>Beskrivelse</th>
            <th class="amount">Beløb</th>
            <?php if($debtor->getDBQuery()->getFilter("status") == -3): ?>
                <th class="amount">Afskrevet</th>
            <?php endif; ?>
            <th>Sendt</th>
            <th><?php print(safeToHtml($translation->get($debtor->get('type').' due date'))); ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <?php /*
    <tfoot>
        <?php
        $due_total = 0;
        $sent_total = 0;
        $total = 0;

        for($i = 0, $max = count($posts); $i < $max; $i++) {
            if($posts[$i]["due_date"] < date("Y-m-d") && ($posts[$i]["status"] == "created" OR $posts[$i]["status"] == "sent")) {
                $due_total += $posts[$i]["total"];
            }
            if($posts[$i]["status"] == "sent") {
                $sent_total += $posts[$i]["total"];
            }
            $total += $posts[$i]["total"];
        }
        if($debtor->get("type") == "invoice") {
            ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="2">Forfaldne:</td>
                <td class="amount"><?php print(number_format($due_total, 2, ",",".")); ?> &nbsp; </td>
                <td colspan="4">&nbsp;</td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2">Udestående (sendt):</td>
            <td class="amount"><?php print(number_format($sent_total, 2, ",",".")); ?> &nbsp; </td>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2">Total:</td>
            <td class="amount"><?php print(number_format($total, 2, ",",".")); ?> &nbsp; </td>
            <td colspan="4">&nbsp;</td>
        </tr>
    </tfoot>
    */ ?>
    <tbody>
        <?php
        $total = 0;
        $due_total = 0;
        $sent_total = 0;
        for($i = 0, $max = count($posts); $i < $max; $i++) {
            ?>
            <tr id="i<?php print(intval($posts[$i]["id"])); ?>" class="<?php if(isset($_GET['id']) && $_GET['id'] == $posts[$i]['id']) echo 'fade'; ?>">
                <td><?php print(safeToHtml($posts[$i]["number"])); ?></td>
                <td class="number"><?php print(safeToHtml($posts[$i]['contact']['number'])); ?></td>
                <td><a href="<?php echo $contact_module->getPath(); ?>contact.php?id=<?php print(safeToHtml($posts[$i]["contact_id"])); ?>"><?php print(safeToHtml($posts[$i]["name"])); ?></a></td>
                <td><a href="view.php?id=<?php print(intval($posts[$i]["id"])); ?>"><?php ($posts[$i]["description"] != "") ? print(safeToHtml($posts[$i]["description"])) : print("[Ingen beskrivelse]"); ?></a></td>
                <td class="amount"><?php print(number_format($posts[$i]["total"], 2, ",",".")); ?> &nbsp; </td>


                <?php
                if($debtor->getDBQuery()->getFilter("status") == -3) {
                    ?>
                    <td class="amount"><?php if ($posts[$i]["deprication"]) print(number_format($posts[$i]["deprication"], 2, ",",".")); ?> &nbsp; </td>
                    <?php
                }
                ?>
                <td class="date">
                    <?php
                    if ($posts[$i]["status"] != "created") {
                        print(safeToHtml($posts[$i]["dk_date_sent"]));
                    }
                    else {
                        print("Nej");
                    }
                    ?>
          </td>
                <td class="date">
                    <?php

                    if($debtor->get('type') == "invoice" && $posts[$i]['status'] == "sent" && $posts[$i]['arrears'] != 0) {
                        $arrears = " (".number_format($posts[$i]['arrears'], 2, ",", ".").")";
                    } else {
                        $arrears = "";
                    }

                    if($posts[$i]["status"] == "executed" || $posts[$i]["status"] == "cancelled") {
                        echo safeToHtml($translation->get($posts[$i]["status"]));
                    } elseif($posts[$i]["due_date"] < date("Y-m-d")) {
                        print("<span class=\"due\">".safeToHtml($posts[$i]["dk_due_date"].$arrears)."</span>");
                    } else {
                        print(safeToHtml($posts[$i]["dk_due_date"].$arrears));
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if($posts[$i]["locked"] == false) {
                        ?>
                        <a class="edit" href="edit.php?id=<?php print(intval($posts[$i]["id"])); ?>">Ret</a>
                        <?php if ($posts[$i]["status"] == "created"): ?>
                        <a class="delete" title="Er du sikker på, at du vil slette denne <?php echo safeToHtml($translation->get($debtor->get('type').' title')); ?>?" href="list.php?id=<?php print(intval($posts[$i]["id"])); ?>&amp;action=delete&amp;use_stored=true">Slet</a>
                        <?php endif; ?>
                        &nbsp;
                        <?php
                    }
                    ?>
                    </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>


<?php echo $debtor->getDBQuery()->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>