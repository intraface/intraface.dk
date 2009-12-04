<div id="colOne">

    <h1><?php e(t('Procurement')); ?></h1>

    <?php echo $procurement->error->view(); ?>

    <ul class="options">
        <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
        <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
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
            <td><?php e(t('Supplier')); ?></td>
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
                        <a href="<?php e(url(null, array('add_contact' => 1))); ?>">Tilknyt</a>
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
                        <a class="edit" href="<?php e(url('choosecontact')); ?>"><?php e(t('Change')); ?></a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td><?php e(t('Buy from')); ?></td>
            <td><?php e(__($procurement->get('from_region'), 'procurement')); ?>
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
                <th><?php e(t('Stated')); ?></th>
                <td>
                    <?php
                        if ($procurement->isStated()) {
                            $module_accounting = $kernel->useModule('accounting');
                            e($procurement->get('dk_date_stated'));
                            echo ' <a href="'.$module_accounting->getPath().'voucher/'.$procurement->get('voucher_id').'">Se bilag</a>';
                        } else {
                            e('Ikke bogf�rt');
                            if ($procurement->get('paid_date') != '0000-00-00') { ?>
                                <a href="<?php e(url('state')); ?>"><?php e(__('state')); ?></a>
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
                <li><a href="<?php e(url(null, array('status'=>'recieved'))); ?>" class="confirm">Varen er modtaget</a></li>
                <li><a href="<?php e(url(null, array('status'=>'canceled'))); ?>" class="confirm">Annull�r bestillingen</a></li>
            </ul>
            <?php
        }
        elseif ($procurement->get("status") == "recieved") {
            ?>
            <p>Varen er modtaget <?php e($procurement->get("dk_date_recieved")); ?>.</p>
            <ol class="options">
                <li><a href="<?php e(url(null, array('status'=>'canceled'))); ?>" class="confirm">Annull�r bestillingen</a></li>
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
                <form method="POST" action="<?php e(url()); ?>">
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
                echo '<div class="appendix"><img src="'.$tmp_filehandler->get('icon_uri').'" style="width: 75px; height: 75px; float: left;" /> <div style="padding-left: 10px; width: 50%;"><a target="_blank" href="'.$tmp_filehandler->get('file_uri').'">'.$tmp_filehandler->get('file_name').'</a>
                <a class="delete" href="'.url(null, array('delete_appended_file_id' => $appendix['id'])).'">Slet</a></div><div style="clear: both;"></div></div>';
            }
        }
        ?>

        <form action="<?php e(url()); ?>" method="POST"  enctype="multipart/form-data">
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
            <li><a href="<?php e(url('selectproduct')); ?>">Registrer varer til lager</a></li>
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
                    <td><?php e(__($items[$i]["unit"])); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["unit_purchase_price"], 2, ",", ".")); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["quantity"]*$items[$i]["unit_purchase_price"], 2, ",", ".")); ?></td>
                    <td class="amount"><?php e(number_format($items[$i]["calculated_unit_price"], 2, ",", ".")); ?></td>
                    <td class="buttons">
                        <?php if ($procurement->get("locked") == false) { ?>
                            <a class="edit" href="<?php e(url('item/' .$items[$i]["id"], array('edit'))); ?>">Ret</a>
                            <a class="delete" href="<?php e(url(null, array('delete_item_id' => $items[$i]["id"]))); ?>">Slet</a>
                        <?php } ?>&nbsp;
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
            <p>Prisen for varerne registreret på lageret overstiger prisen for varerne angivet på indkøbet.</p>
        </div>
        <?php
    }
    ?>
<?php endif; ?>

<?php endif; ?>
</div>
