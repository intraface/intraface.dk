<h1>
    <?php
        e(ucfirst(t($context->getDebtor()->get("type").'s')));
        if (!empty($contact) AND is_object($contact) && $contact->address->get('name') != '') { ?>
            : <?php e($contact->address->get('name')); ?>
        <?php }

        if (!empty($product) AND is_object($product) && $product->get('name') != '') { ?>
            med produkt: <?php e($product->get('name')); ?>
            <?php if (!empty($variation) AND is_object($variation) AND $variation->getName() != '') { ?>
                - <?php e($variation->getName()); ?>
            <?php }
        }
    ?>
</h1>



<?php if ($context->getKernel()->intranet->address->get('id') == 0): ?>
    <p>Du mangler at udfylde adresse til dit intranet. Det skal du gøre, før du kan oprette en <?php e(strtolower(__($context->getDebtor()->get('type')))); ?>.
    <?php if ($context->getKernel()->user->hasModuleAccess('administration')): ?>
        <?php
        $module_administration = $context->getKernel()->useModule('administration');
        ?>
        <a href="<?php e(url('../../../administration/intranet/'.$context->getKernel()->intranet->getId(), array('edit'))); ?>"><?php e(t('Fill in address')); ?></a>.
    <?php else: ?>
        Du har ikke adgang til at rette adresseoplysningerne, det må du bede din administrator om at gøre.
    <?php endif; ?>
    </p>
<?php elseif (!$context->getDebtor()->isFilledIn()): ?>

    <?php if ($context->getDebtor()->get('type') == 'credit_note'): ?>
        <p>Du har endnu ikke oprettet nogen. Kreditnotaer oprettes fra en fakturaer.</p>
    <?php else: ?>

        <p><?php e(t('None has been created yet')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(__('Create '.$context->getDebtor()->get('type'))); ?></a>.</p>

    <?php endif; ?>
<?php else: ?>

<ul class="options">
    <?php if (!empty($contact) AND is_object($contact) AND $context->getDebtor()->get("type") != "credit_note"): ?>
        <li><a href="edit.php?type=<?php e($context->getDebtor()->get("type")); ?>&amp;contact_id=<?php e($contact->get("id")); ?>"><?php e(__('Create')); ?></a></li>
        <li><a href="<?php e($contact_module->getPath()); ?>contact.php?id=<?php e($contact->get('id')); ?>">Vis kontakten</a>
    <?php else: ?>
        <?php if (!empty($_GET['product_id'])): ?>
            <li><a href="<?php e($product_module->getPath()); ?>product.php?id=<?php e($product->get('id')); ?>">Vis produktet</a>
        <?php endif; ?>
        <li><a href="<?php e(url(null, array('create'))); ?>"><?php e(__('Create')); ?></a></li>
    <?php endif; ?>
    <li><a class="excel" href="<?php e(url(null . '.excel', array('use_stored' => 'true'))); ?>"><?php e(t('Excel')); ?></a></li>
</ul>


<?php echo $context->getDebtor()->error->view(); ?>

<?php if (!isset($_GET['$contact_id'])): ?>

    <fieldset class="hide_on_print">
        <legend><?php e(__('Advanced search')); ?></legend>
        <form method="get" action="<?php e(url(null)); ?>">
        <label><?php e(__('Text')); ?>
            <input type="text" name="text" value="<?php e($context->getDebtor()->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label><?php e(__('Status')); ?>
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
            <?php if ($context->getDebtor()->get("type") == "invoice"): ?>
            <option value="-3"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == -3) echo ' selected="selected"';?>>Afskrevet</option>
            <?php endif; ?>
            <option value="0"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
            <option value="1"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == 1) echo ' selected="selected"';?>>Sendt</option>
            <option value="2"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == 2) echo ' selected="selected"';?>>Afsluttet</option>
            <option value="3"<?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
        </select>
        </label>
        <!-- sortering bør være placeret ved at man klikker på en overskrift i stedet - og så bør man kunne sortere på det hele -->
        <label><?php e(__('Sorting')); ?>
        <select name="sorting">
            <?php foreach(array(0 => ucfirst($context->getDebtor()->get('type')).' number descending', 1 => ucfirst($context->getDebtor()->get('type')).' number ascending', 2 => 'Contact number', 3 => 'Contact name') AS $key => $description): ?>
                <option value="<?php e($key); ?>"<?php if ($context->getDebtor()->getDBQuery()->getFilter("sorting") == $key) echo ' selected="selected"';?>><?php e(t($description)); ?></option>
            <?php endforeach; ?>
        </select>
        </label>
        <br />

        <label><?php e(t('Date interval'))?>
            <select name="date_field">
                <?php foreach(array('this_date' => ucfirst($context->getDebtor()->get('type')).' date', 'date_created' => 'Date created', 'date_sent' => 'Date sent', 'date_executed' => 'Date executed', 'data_cancelled' => 'Date cancelled') AS $field => $description): ?>
                    <option value="<?php e($field); ?>" <?php if ($context->getDebtor()->getDBQuery()->getFilter("date_field") == $field) echo ' selected="selected"';?>><?php e(t($description)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label><?php e(t('From', 'common'))?>
            <input type="text" name="from_date" id="date-from" value="<?php e($context->getDebtor()->getDBQuery()->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label><?php e(t('To', 'common'))?>
            <input type="text" name="to_date" value="<?php e($context->getDebtor()->getDBQuery()->getFilter("to_date")); ?>" />
        </label>

        <span>
        <input type="hidden" name="type" value="<?php e($context->getDebtor()->get("type")); ?>" />
        <input type="hidden" name="contact_id" value="<?php e($context->getDebtor()->getDBQuery()->getFilter('contact_id')); ?>" />
        <input type="hidden" name="product_id" value="<?php e($context->getDebtor()->getDBQuery()->getFilter('product_id')); ?>" />
        <input type="submit" name="search" value="Find" />
        </span>
        </form>
    </fieldset>

<?php endif; ?>

<table class="stripe">
    <caption><?php e(__($context->getDebtor()->get("type"))); ?></caption>
    <thead>
        <tr>
            <th><?php e(__('No.')); ?></th>
            <th colspan="2"><?php e(__('Contact')); ?></th>
            <th><?php e(__('Description')); ?></th>
            <th class="amount"><?php e(__('Amount')); ?></th>
            <?php if ($context->getDebtor()->getDBQuery()->getFilter("status") == -3): ?>
                <th class="amount"><?php e(__('Depreciated')); ?></th>
            <?php endif; ?>
            <th><?php e(__('Sent')); ?></th>
            <th><?php e(__($context->getDebtor()->get('type').' due date')); ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <?php /*
    <tfoot>
        <?php
        $due_total = 0;
        $sent_total = 0;
        $total = 0;

        for ($i = 0, $max = count($posts); $i < $max; $i++) {
            if ($posts[$i]["due_date"] < date("Y-m-d") && ($posts[$i]["status"] == "created" OR $posts[$i]["status"] == "sent")) {
                $due_total += $posts[$i]["total"];
            }
            if ($posts[$i]["status"] == "sent") {
                $sent_total += $posts[$i]["total"];
            }
            $total += $posts[$i]["total"];
        }
        if ($context->getDebtor()->get("type") == "invoice") {
            ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="2">Forfaldne:</td>
                <td class="amount"><?php e(number_format($due_total, 2, ",",".")); ?> &nbsp; </td>
                <td colspan="4">&nbsp;</td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2">Udestående (sendt):</td>
            <td class="amount"><?php e(number_format($sent_total, 2, ",",".")); ?> &nbsp; </td>
            <td colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2">Total:</td>
            <td class="amount"><?php e(number_format($total, 2, ",",".")); ?> &nbsp; </td>
            <td colspan="4">&nbsp;</td>
        </tr>
    </tfoot>
    */ ?>
    <tbody>
        <?php
        $total = 0;
        $due_total = 0;
        $sent_total = 0;
        foreach ($context->getPosts() as $post) {
            ?>
            <tr id="i<?php e($post["id"]); ?>">
                <td><?php e($post["number"]); ?></td>
                <td class="number"><?php e($post['contact']['number']); ?></td>
                <td><a href="<?php // e($contact_module->getPath()); ?><?php e(url('../../../contact/' . $post["contact_id"])); ?>"><?php e($post["name"]); ?></a></td>
                <td><a href="<?php e(url($post["id"])); ?>"><?php ($post["description"] != "") ? e($post["description"]) : e("[Ingen beskrivelse]"); ?></a></td>
                <td class="amount"><?php e(number_format($post["total"], 2, ",",".")); ?> &nbsp; </td>


                <?php
                if ($context->getDebtor()->getDBQuery()->getFilter("status") == -3) {
                    ?>
                    <td class="amount"><?php if ($post["deprication"]) e(number_format($post["deprication"], 2, ",",".")); ?> &nbsp; </td>
                    <?php
                }
                ?>
                <td class="date">
                    <?php
                    if ($post["status"] != "created") {
                        e($post["dk_date_sent"]);
                    } else {
                        e(t('No', 'common'));
                    }
                    ?>
          </td>
                <td class="date">
                    <?php

                    if ($context->getDebtor()->get('type') == "invoice" && $post['status'] == "sent" && $post['arrears'] != 0) {
                        $arrears = " (".number_format($post['arrears'], 2, ",", ".").")";
                    } else {
                        $arrears = "";
                    }

                    if ($post["status"] == "executed" || $post["status"] == "cancelled") {
                        e(__($post["status"]));
                    } elseif ($post["due_date"] < date("Y-m-d")) { ?>
                        <span class="due"><?php e($post["dk_due_date"].$arrears); ?></span>
                    <?php
                    } else {
                        e($post["dk_due_date"].$arrears);
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($post["locked"] == false) {
                        ?>
                        <a class="edit" href="<?php e(url($post["id"], array('edit'))); ?>"><?php e(__('Edit')); ?></a>
                        <?php if ($post["status"] == "created"): ?>
                        <a class="delete" title="<?php e(__('Are you sure?')); ?>" href="<?php e(url($post["id"], array('delete'))); ?>"><?php e(__('Delete')); ?></a>
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

<?php echo $context->getDebtor()->getDBQuery()->display('paging'); ?>

<?php endif; ?>
