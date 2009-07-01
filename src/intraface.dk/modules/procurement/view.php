<?php
require('../../include_first.php');

$module_procurement = $kernel->module("procurement");
$shared_filehandler = $kernel->useShared('filehandler');
$shared_filehandler->includeFile('AppendFile.php');
$translation = $kernel->getTranslation('procurement');

$procurement = new Procurement($kernel, $_GET["id"]);
$filehandler = new FileHandler($kernel);
$append_file = new AppendFile($kernel, 'procurement_procurement', $procurement->get('id'));

# set status
if (isset($_GET['status'])) {
    $procurement->setStatus($_GET['status']);
}

# set betalt
if (isset($_POST['dk_paid_date'])) {
    $procurement->setPaid($_POST['dk_paid_date']);
    if ($kernel->user->hasModuleAccess('accounting')) {
        header('location: state.php?id=' . intval($procurement->get("id")));
        exit;
    }
}

# slet item
if (isset($_GET['delete_item_id'])) {
    $procurement->loadItem((int)$_GET['delete_item_id']);
    $procurement->item->delete();
}

# tilf�j kontakt
if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
    if ($kernel->user->hasModuleAccess('contact')) {
        $contact_module = $kernel->useModule('contact');

        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module_procurement->getPath()."view.php?id=".$procurement->get('id'));
        $redirect->askParameter('contact_id');
        $redirect->setIdentifier('contact');

        if ($procurement->get('contact_id') != 0) {
            header("location: ".$url."&contact_id=".$procurement->get('contact_id'));
        }
        else {
            header("location: ".$url);
        }
    }
    else {
        trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
    }
}

// tilf�j bilag med redirect til filemanager
if (isset($_POST['append_file_choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
    $redirect = new Intraface_Redirect($kernel);
    $module_filemanager = $kernel->useModule('filemanager');
    $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_procurement->getPath().'view.php?id='.$procurement->get('id'));
    $redirect->setIdentifier('file_handler');
    $redirect->askParameter('file_handler_id', 'multiple');
    header("Location: ".$url);
    exit;
}

// upload billag
if (isset($_POST['append_file_submit'])) {
    if (isset($_FILES['new_append_file'])) {

        $filehandler->createUpload();
        $filehandler->upload->setSetting('max_file_size', '2000000');
        if ($id = $filehandler->upload->upload('new_append_file')) {
            $append_file->addFile(new FileHandler($kernel, $id));
        }
        $procurement->error->merge($filehandler->error->getMessage());

    }
}

# slet bilag
if (isset($_GET['delete_appended_file_id'])) {
    $append_file->delete((int)$_GET['delete_appended_file_id']);
}

# tilf�j produkt
if (isset($_GET['add_item'])) {
    if ($kernel->user->hasModuleAccess('product')) {
        $redirect = Intraface_Redirect::factory($kernel, 'go');
        $module_product = $kernel->useModule('product');
        $url = $redirect->setDestination($module_product->getPath().'select_product.php?set_quantity=1', $module_procurement->getPath().'set_purchase_price.php?id='.$procurement->get('id'));
        $redirect->askParameter('product_id', 'multiple');
        header('location: '.$url);
        exit;
    }
    else {
        trigger_error('You need access to the product module to do this!', E_USER_ERROR);
        exit;
    }
}

#retur
if (isset($_GET['return_redirect_id'])) {
    $redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($redirect->get('identifier') == 'contact') {
        if ($kernel->user->hasModuleAccess('contact')) {
            $contact_module = $kernel->useModule('contact');
            $contact = new Contact($kernel, $redirect->getParameter('contact_id'));
            if ($contact->get('id') != 0) {
                $procurement->setContact($contact);
            }
            else {
                $procurement->error->set('Ingen gyldig kontakt blev valgt');
            }

        }
        else {
            trigger_error('You need access to the contact module!', E_USER_ERROR);
            exit;
        }
    }
    elseif ($redirect->get('identifier') == 'file_handler') {

        $file_handler_id = $redirect->getParameter('file_handler_id');
        foreach ($file_handler_id as $id) {
            $append_file->addFile(new FileHandler($kernel, $id));
        }

    }
}

$page = new Intraface_Page($kernel);
$page->start("Indk�b");

?>

<div id="colOne">

    <h1>Indk�b</h1>

    <?php echo $procurement->error->view(); ?>

    <ul class="options">
        <li><a href="edit.php?id=<?php e($procurement->get("id")); ?>">Ret</a></li>
        <li><a href="index.php?use_stored=true">Luk</a></li>
    </ul>

    <p><?php e($procurement->get("description")); ?></p>

    <table>
        <caption>Oplysninger</caption>
        <tbody>
        <tr>
            <td>Nummer</td>
            <td><?php e($procurement->get("number")); ?></td>
        </tr>

        <tr>
            <td>Fakturadato</td>
            <td><?php e($procurement->get("dk_invoice_date")); ?></td>
        </tr>
        <tr>
            <td>Leverand�r</td>
            <td><?php e($procurement->get("vendor")) ?></td>
        </tr>
        <?php
        if ($kernel->user->hasModuleAccess('contact')) {
            ?>
            <tr>
                <td>Kontakt</td>
                <td>
                    <?php
                    if ($procurement->get('contact_id') == 0) {
                        ?>
                        <a href="view.php?id=<?php e($procurement->get('id')); ?>&amp;add_contact=1">Tilknyt</a>
                        <?php
                    } else {
                        $module_contact = $kernel->useModule('contact');
                        $contact = new Contact($kernel, $procurement->get('contact_id'));
                        if ($contact->get('id') != 0) {
                            ?>
                            <a href="<?php e($module_contact->getPath()."contact.php?id=".$procurement->get('contact_id')); ?>"><?php e($contact->get('name')); ?></a>
                            <?php
                        } else {
                            echo 'Ugyldig kontakt';
                        }
                        ?>
                        <a class="edit" href="view.php?id=<?php e($procurement->get('id')); ?>&amp;add_contact=1">�ndre</a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td>K�b fra</td>
            <td><?php e($translation->get($procurement->get('from_region'), 'procurement')); ?>
            </td>
        </tr>
        <tr>
            <td>Pris samlet</td>
            <td><?php e($procurement->get("dk_total_price")); ?> (incl. moms)</td>
        </tr>
        <tr>
            <td>Pris for varer (eks. administrationsgebyr og forsendelse)</td>
            <td><?php e($procurement->get("dk_price_items")); ?> (excl. moms)</td>
        </tr>
        <tr>
            <td>Pris for forsendelse, gebyr osv</td>
            <td><?php e($procurement->get("dk_price_shipment_etc")); ?></td>
        </tr>
        <tr>
            <td>Moms</td>
            <td><?php e($procurement->get("dk_vat")); ?></td>
        </tr>
        <?php if ($kernel->user->hasModuleAccess('accounting')): ?>
            <tr>
                <th>Bogf�rt</th>
                <td>
                    <?php
                        if ($procurement->isStated()) {
                            $module_accounting = $kernel->useModule('accounting');
                            e($procurement->get('dk_date_stated'));
                            echo ' <a href="'.$module_accounting->getPath().'voucher.php?id='.$procurement->get('voucher_id').'">Se bilag</a>';
                        } else {
                            e('Ikke bogf�rt');
                            if ($procurement->get('paid_date') != '0000-00-00') { ?>
                                <a href="state.php?id=<?php e($procurement->get("id")); ?>"><?php e($translation->get('state')); ?></a>
                            <?php }

                        }
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>


<div id="colTwo">

    <div class="box">
        <h2>Status</h2>

        <?php
        if ($procurement->get("status") == "ordered") {
            ?>
            <p><strong>Varen er bestilt, men endnu ikke modtaget.</strong></p>
            <?php
            if ($procurement->get("dk_delivery_date") != "") {
                ?>
                <p>Forventet levering <?php e($procurement->get("dk_delivery_date")); ?>.</p>
                <?php
            }
            else {
                ?>
                <p>Forventet levering ikke angivet.</p>
                <?php
            }
            ?>
            <ul class="options">
                <li><a href="view.php?id=<?php e($procurement->get('id')); ?>&status=recieved" class="confirm">Varen er modtaget</a></li>
                <li><a href="view.php?id=<?php e($procurement->get('id')); ?>&status=canceled" class="confirm">Annull�r bestillingen</a></li>
            </ul>
            <?php
        }
        elseif ($procurement->get("status") == "recieved") {
            ?>
            <p>Varen er modtaget <?php e($procurement->get("dk_date_recieved")); ?>.</p>
            <ol class="options">
                <li><a href="view.php?id=<?php e($procurement->get('id')); ?>&status=canceled" class="confirm">Annull�r bestillingen</a></li>
            </ol>

            <?php
        } else {
            ?>
            <p class="highlight">Bestillingen er annulleret <?php e($procurement->get("dk_date_canceled")); ?>.</p>
            <?php
        }
        ?>

        <?php
        if ($procurement->get('status') != 'canceled') {
            ?>

            <h2>Betaling</h2>

            <?php
            if ($procurement->get("paid_date") != "0000-00-00") {
                ?>
                <p>Varen er betalt <?php e($procurement->get("dk_paid_date")); ?>.</p>
                <?php
            } else {
                ?>
                <p><strong>Varen er endnu ikke betalt.</strong></p>
                <?php
                if ($procurement->get("dk_payment_date") != "") {

                    if (strtotime($procurement->get("payment_date")) < time()) {
                        $class = "highlight";
                    } else {
                        $class = "";
                    }
                    ?>
                    <p class="<?php e($class); ?>">Betales senest <?php e($procurement->get("dk_payment_date")); ?>.</p>
                    <?php
                } else {
                    ?>
                    <p>Betalingsdato ikke angivet.</p>
                    <?php
                }
                ?>
                <form method="POST" action="view.php?id=<?php e($procurement->get('id')); ?>">
                <label for="dk_paid_date">Betalt dato <input type="text" name="dk_paid_date" id="dk_paid_date" value="<?php e(date("d-m-Y")); ?>" size="10" /></label>
                <input type="submit" name="paid" value="Betalt" />
                </form>
                <?php
            }
        }
        ?>
    </div>
    <div class="box">
        <h2>Bilag</h2>

        <?php
        $appendix_list = $append_file->getList();

        if (count($appendix_list) > 0) {
            foreach ($appendix_list AS $appendix) {
                $tmp_filehandler = new FileHandler($kernel, $appendix['file_handler_id']);
                echo '<div class="appendix"><img src="'.$tmp_filehandler->get('icon_uri').'" style="width: 75px; height: 75px; float: left;" /> <div style="padding-left: 10px; width: 50%;"><a target="_blank" href="'.$tmp_filehandler->get('file_uri').'">'.$tmp_filehandler->get('file_name').'</a> <a class="delete" href="view.php?id='.$procurement->get('id').'&delete_appended_file_id='.$appendix['id'].'">Slet</a></div><div style="clear: both;"></div></div>';
            }
        }
        ?>

        <form action="<?php e($_SERVER['PHP_SELF'].'?id='.$procurement->get('id')); ?>" method="POST"  enctype="multipart/form-data">
        <?php
        $filehandler_html = new FileHandlerHTML($filehandler);
        $filehandler_html->printFormUploadTag('', 'new_append_file', 'append_file_choose_file', array('type'=>'only_upload', 'include_submit_button_name' => 'append_file_submit'));
        ?>
        </form>
    </div>
</div>

<br />

<div style="clear: both;">

<?php if ($kernel->user->hasModuleAccess('product')): ?>
    <?php
    $product_module = $kernel->useModule('product');

    if ($procurement->get("locked") == false) {
        ?>
        <ul class="options">
            <li><a href="view.php?id=<?php e($procurement->get("id")); ?>&amp;add_item=1">Registrer varer til lager</a></li>
        </ul>
        <?php
    }
    ?>

    <?php
    $procurement->loadItem();
    $items = $procurement->item->getList();

    if (count($items) > 0):
    ?>
    <table class="stripe">
        <caption>Varer</caption>
        <thead>
            <tr>
                <th>Varenr.</th>
                <th>Beskrivelse</th>
                <th style="text-align: right">Antal</th>
                <th>&nbsp;</th>
                <th style="text-align: right">Indk�bspris</th>
                <th style="text-align: right">I alt</th>
                <th style="text-align: right">Kostpris</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;


            for ($i = 0, $max = count($items); $i<$max; $i++) {
                $total += $items[$i]["quantity"] * $items[$i]["unit_purchase_price"];
                ?>
                <tr id="i<?php e($items[$i]["id"]); ?>" <?php if (isset($_GET['item_id']) && $_GET['item_id'] == $items[$i]['id']) print(' class="fade"'); ?>>
                    <td><?php e($items[$i]["number"]); ?></td>
                    <td><?php e($items[$i]["name"]); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["quantity"], 2, ",", ".")); ?></td>
                    <td><?php e($translation->get($items[$i]["unit"])); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["unit_purchase_price"], 2, ",", ".")); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["quantity"]*$items[$i]["unit_purchase_price"], 2, ",", ".")); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["calculated_unit_price"], 2, ",", ".")); ?></td>
                    <td class="buttons">
                        <?php
                        if ($procurement->get("locked") == false) {
                            ?>
                            <a class="edit" href="item_edit.php?procurement_id=<?php e($procurement->get('id')); ?>&amp;id=<?php e($items[$i]["id"]); ?>">Ret</a>
                            <a class="delete" href="view.php?id=<?php e($procurement->get("id")); ?>&amp;delete_item_id=<?php e($items[$i]["id"]); ?>">Slet</a>
                            <?php
                        }
                        ?>&nbsp;
                    </td>
                </tr>
                <?php
            }

            ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">Total:</td>
                <td class="amount"><?php e(number_format($total, 2, ",", ".")); ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <?php
    if ($total > $procurement->get("price_items")) {
        ?>
        <div class="box">
            <p>Prisen for varerne registreret p� lageret overstiger prisen for varerne angivet p� indk�bet.</p>
        </div>
        <?php
    }
    ?>
<?php endif; ?>

<?php endif; ?>
</div>

<?php
$page->end();
?>