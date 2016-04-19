<?php
$reminder = $context->getReminder();
$contact_id = $context->query('contact_id');
$reminders = $context->getReminders();
?>

<h1><?php e(t('Reminders')); ?></h1>

<?php if ($contact_id) : ?>
    <ul class="options">
        <li><a href="<?php e(url(null, array('create', 'contact_id' => $contact_id))); ?>"><?php e(t('Create')); ?></a></li>
        <li><a href="<?php e(url('../../contact/' . $contact_id)); ?>"><?php e(t('Go to contact')); ?></a>
    </ul>
<?php endif; ?>


<?php if (!$reminder->isFilledIn()) : ?>

<p>Der er ikke oprettet nogen rykkere endnu. Du har nok nogle gode kunder. Rykkere oprettes fra en faktura.</p>

<?php else : ?>

<?php
echo $context->getReminder()->error->view();
?>

<?php
if ($contact_id == 0) {
    ?>
    <form method="get" action="<?php e(url()); ?>">
    <fieldset>
        <legend><?php e(t('Search')); ?></legend>
        <?php e(t('Text')); ?> <input type="text" name="text" value="<?php e($reminder->getDBQuery()->getFilter("text")); ?>" />
        <?php e(t('Status')); ?>
        <select name="status">
            <option value="-1">Alle</option>
            <option value="-2"<?php if ($reminder->getDBQuery()->getFilter("status") == -2) {
                echo ' selected="selected"';
}?>>�bne</option>
            <option value="0"<?php if ($reminder->getDBQuery()->getFilter("status") == 0) {
                echo ' selected="selected"';
}?>>Oprettet</option>
            <option value="1"<?php if ($reminder->getDBQuery()->getFilter("status") == 1) {
                echo ' selected="selected"';
}?>>Sendt</option>
            <option value="2"<?php if ($reminder->getDBQuery()->getFilter("status") == 2) {
                echo ' selected="selected"';
}?>>Afsluttet</option>
            <option value="3"<?php if ($reminder->getDBQuery()->getFilter("status") == 3) {
                echo ' selected="selected"';
}?>>Annulleret</option>
        </select>
        <?php e(t('From date')); ?> <input type="text" name="from_date" value="<?php e($reminder->getDBQuery()->getFilter("from_date")); ?>" />
        <?php e(t('To date')); ?> <input type="text" name="to_date" value="<?php e($reminder->getDBQuery()->getFilter("to_date")); ?>" />
        <input type="submit" name="search" value="<?php e(t('Search')); ?>" />
    </fieldset>
    </form>
    <?php
}
?>

<table class="stripe">
<thead>
    <tr>
        <th><?php e(t('No.')); ?></th>
        <th><?php e(t('Contact')); ?></th>
        <th><?php e(t('Description')); ?></th>
        <th><?php e(t('Sent')); ?></th>
        <th><?php e(t('Send as')); ?></th>
        <th><?php e(t('Due date')); ?></th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php
    foreach ($context->getReminders() as $r) {
        ?>
        <tr id="i<?php e($r["id"]); ?>"<?php if (isset($_GET['id']) && $_GET['id'] == $r['id']) {
            print(" class=\"fade\"");
} ?>>
            <td class="number"><?php e($r["number"]); ?></td>
            <td><a href="<?php e(url('../../contact/' . $r["contact_id"])); ?>"><?php e($r["name"]); ?></a></td>
            <td><a href="<?php e(url($r["id"])); ?>"><?php (trim($r["description"] != "")) ? e($r["description"]) : e('['.t("No description").']'); ?></a></td>
            <td class="date">
                <?php
                if ($r["status"] != "created") {
                    e($r["dk_date_sent"]);
                } else {
                    e(t('No'));
                }
                ?>
      </td>
            <td><?php e($r["send_as"]); ?></td>
            <td class="date">
                <?php
                if ($r["status"] == "executed" || $r["status"] == "canceled") {
                    e($r["status"]);
                } elseif ($r["due_date"] < date("Y-m-d")) { ?>
                    <span class="red"><?php e($r["dk_due_date"]); ?></span>
                <?php
                } else {
                    e($r["dk_due_date"]);
                }
                ?>
            </td>
            <td class="buttons">
                <?php
                if ($r["locked"] == 0) {
                    ?>
                    <a class="edit" href="<?php e(url($r["id"], array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                    <?php if ($r["status"] == "created") : ?>
                    <a class="delete" href="<?php e(url($r["id"], array('delete'))); ?>"><?php e(t('Delete')); ?></a>
                    <?php endif; ?>
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
<?php echo $reminder->getDBQuery()->display('paging'); ?>

<?php endif; ?>