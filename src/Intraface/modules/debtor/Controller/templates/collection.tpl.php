<h1>
    <?php
        e(ucfirst(t($context->getType().'s')));
    if (!empty($contact) and is_object($contact) && $contact->address->get('name') != '') { ?>
            : <?php e($contact->address->get('name')); ?>
        <?php                                                                                                             }

    if (!empty($product) and is_object($product) && $product->get('name') != '') { ?>
            <?php e(t('with product'))?>:
            <?php e($product->get('name')); ?>
            <?php if (!empty($variation) and is_object($variation) and $variation->getName() != '') { ?>
                - <?php e($variation->getName()); ?>
            <?php }
    }
    ?>
</h1>

<?php if ($context->getKernel()->intranet->address->get('id') == 0) : ?>
    <p>
        <?php e(t('You have not filled in the address for your intranet. Do that before you can create your first')); ?>
        <?php e(strtolower(t($context->getType()))); ?>.
    <?php if ($context->getKernel()->user->hasModuleAccess('administration')) : ?>
        <?php
        $module_administration = $context->getKernel()->useModule('administration');
        ?>
        <a href="<?php e(url('../../../administration/intranet', array('edit'))); ?>"><?php e(t('Fill in address')); ?></a>.
    <?php else : ?>
        <?php e(t('You do not have access to edit the address information. Please ask your administrator to do that.')); ?>
        <?php e(strtolower(t($debtor->getType()))); ?>.
    <?php endif; ?>
    </p>
<?php elseif (!$debtor->isFilledIn()) : ?>

    <?php if ($debtor->getType() == 'credit_note') : ?>
        <p>
            <?php e(t('You have not created any. Credit notes are created from invoices.')); ?>
        </p>
            <?php else : ?>

        <p><?php e(t('None has been created yet')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create '.$context->getType())); ?></a>.</p>

    <?php endif; ?>
<?php else : ?>

<ul class="options">
    <?php if (!empty($contact) and is_object($contact) and $debtor->getType() != "credit_note") : ?>
        <li><a href="<?php e(url(null, array('create', 'contact_id' => $contact->get("id")))); ?>"><?php e(t('Create')); ?></a></li>
    <?php else : ?>
        <?php if (isset($variation) && isset($product)) : ?>
            <?php $module_product = $context->getKernel()->useModule('product'); ?>
            <li><a href="<?php e(url($module_product->getPath().$product->get('id').'/variation/'.$variation->getId()));  ?>"><?php e(t('Show product')); ?></a></li>
        <?php elseif (isset($product)) : ?>
            <?php $module_product = $context->getKernel()->useModule('product'); ?>
           <li><a href="<?php e(url($module_product->getPath().$product->get('id')));  ?>"><?php e(t('Show product')); ?></a></li>
        <?php endif; ?>
        <li><a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create')); ?></a></li>
    <?php endif; ?>
    <li><a class="excel" href="<?php e(url(null . '.xls', array('use_stored' => 'true'))); ?>"><?php e(t('Excel')); ?></a> (<a href="<?php e(url(null . '.xls', array('use_stored' => 'true', 'simple' => 'true'))); ?>"><?php e(t('Simple')); ?></a>)</li>
</ul>


<?php echo $debtor->error->view(); ?>

<?php if (!isset($_GET['$contact_id'])) : ?>

    <fieldset class="hide_on_print">
        <legend><?php e(t('Advanced search')); ?></legend>
        <form method="get" action="<?php e(url(null)); ?>">
        <label><?php e(t('Text')); ?>
            <input type="text" name="text" value="<?php e($debtor->getDBQuery()->getFilter("text")); ?>" />
        </label>
        <label><?php e(t('Status')); ?>
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($debtor->getDBQuery()->getFilter("status") == -2) {
                echo ' selected="selected"';
}?>><?php e(t('Open')); ?></option>
            <?php if ($debtor->getType() == "invoice") : ?>
            <option value="-3"<?php if ($debtor->getDBQuery()->getFilter("status") == -3) {
                echo ' selected="selected"';
}?>><?php e(t('Depreciated')); ?></option>
            <?php endif; ?>
            <option value="0"<?php if ($debtor->getDBQuery()->getFilter("status") == 0) {
                echo ' selected="selected"';
}?>><?php e(t('Created')); ?></option>
            <option value="1"<?php if ($debtor->getDBQuery()->getFilter("status") == 1) {
                echo ' selected="selected"';
}?>><?php e(t('Sent')); ?></option>
            <option value="2"<?php if ($debtor->getDBQuery()->getFilter("status") == 2) {
                echo ' selected="selected"';
}?>><?php e(t('Executed')); ?></option>
            <option value="3"<?php if ($debtor->getDBQuery()->getFilter("status") == 3) {
                echo ' selected="selected"';
}?>><?php e(t('Cancelled')); ?></option>
        </select>
        </label>
        <!-- sortering b�r v�re placeret ved at man klikker p� en overskrift i stedet - og s� b�r man kunne sortere p� det hele -->
        <label><?php e(t('Sorting')); ?>
        <select name="sorting">
            <?php foreach (array(0 => ucfirst($debtor->getType()).' number descending', 1 => ucfirst($debtor->getType()).' number ascending', 2 => 'Contact number', 3 => 'Contact name') as $key => $description) : ?>
                <option value="<?php e($key); ?>"<?php if ($debtor->getDBQuery()->getFilter("sorting") == $key) {
                    echo ' selected="selected"';
}?>><?php e(t($description)); ?></option>
            <?php endforeach; ?>
        </select>
        </label>
        <br />

        <label><?php e(t('Date interval'))?>
            <select name="date_field">
                <?php foreach (array('this_date' => ucfirst($debtor->getType()).' date', 'date_created' => 'Date created', 'date_sent' => 'Date sent', 'date_executed' => 'Date executed', 'data_cancelled' => 'Date cancelled') as $field => $description) : ?>
                    <option value="<?php e($field); ?>" <?php if ($debtor->getDBQuery()->getFilter("date_field") == $field) {
                        echo ' selected="selected"';
}?>><?php e(t($description)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label><?php e(t('From'))?>
            <input type="text" name="from_date" id="date-from" value="<?php e($debtor->getDBQuery()->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label><?php e(t('To'))?>
            <input type="text" name="to_date" value="<?php e($debtor->getDBQuery()->getFilter("to_date")); ?>" />
        </label>

        <span>
        <input type="hidden" name="type" value="<?php e($debtor->getType()); ?>" />
        <input type="hidden" name="contact_id" value="<?php e($debtor->getDBQuery()->getFilter('contact_id')); ?>" />
        <input type="hidden" name="product_id" value="<?php e($debtor->getDBQuery()->getFilter('product_id')); ?>" />
        <input type="submit" name="search" value="Find" />
        </span>
        </form>
    </fieldset>

<?php endif; ?>

<table class="stripe">
    <caption><?php e(t($debtor->getType())); ?></caption>
    <thead>
        <tr>
            <th><?php e(t('No.')); ?></th>
            <th colspan="2"><?php e(t('Contact')); ?></th>
            <th><?php e(t('Description')); ?></th>
            <th class="amount"><?php e(t('Amount')); ?></th>
            <?php if ($debtor->getDBQuery()->getFilter("status") == -3) : ?>
                <th class="amount"><?php e(t('Depreciated')); ?></th>
            <?php endif; ?>
            <th><?php e(t('Sent')); ?></th>
            <th><?php e(t($debtor->getType().' due date')); ?></th>
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
        if ($debtor->get("type") == "invoice") {
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
            <td colspan="2">Udest�ende (sendt):</td>
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
        foreach ($posts as $post) {
            ?>
            <tr id="i<?php e($post["id"]); ?>">
                <td><?php e($post["number"]); ?></td>
                <td class="number"><?php e($post['contact']['number']); ?></td>
                <td><a href="<?php // e($contact_module->getPath()); ?><?php e(url('../../../contact/' . $post["contact_id"])); ?>"><?php e($post["name"]); ?></a></td>
                <td><a href="<?php e(url($post["id"])); ?>"><?php ($post["description"] != "") ? e($post["description"]) : e("[Ingen beskrivelse]"); ?></a></td>
                <td class="amount"><?php e(number_format($post["total"], 2, ",", ".")); ?> &nbsp; </td>


                <?php
                if ($debtor->getDBQuery()->getFilter("status") == -3) {
                    ?>
                    <td class="amount"><?php if ($post["deprication"]) {
                        e(number_format($post["deprication"], 2, ",", "."));
} ?> &nbsp; </td>
                    <?php
                }
                ?>
                <td class="date">
                    <?php
                    if ($post["status"] != "created") {
                        e($post["dk_date_sent"]);
                    } else {
                        e(t('No'));
                    }
                    ?>
          </td>
                <td class="date">
                    <?php

                    if ($debtor->getType() == "invoice" && $post['status'] == "sent" && $post['arrears'] != 0) {
                        $arrears = " (".number_format($post['arrears'], 2, ",", ".").")";
                    } else {
                        $arrears = "";
                    }

                    if ($post["status"] == "executed" || $post["status"] == "cancelled") {
                        e(t($post["status"]));
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
                        <a class="edit" href="<?php e(url($post["id"], array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                        <?php if ($post["status"] == "created") : ?>
                        <a class="delete" title="<?php e(t('Are you sure?')); ?>" href="<?php e(url($post["id"], array('delete'))); ?>"><?php e(t('Delete')); ?></a>
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
