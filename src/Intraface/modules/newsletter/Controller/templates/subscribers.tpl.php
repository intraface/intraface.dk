<h1>Modtagere på listen <?php e($list->get('title')); ?></h1>

<ul class="options">

    <li><a href="subscribers.php?list_id=<?php e($list->get('id')); ?>&amp;add_contact=1">Tilføj kontakt</a></li>
    <li><a href="list.php?id=<?php e($list->get('id')); ?>">Luk</a></li>

</ul>

<?php echo $subscriber->error->view(); ?>

<form action="subscribers.php?" method="get" class="search-filter">
    <input type="hidden" name="list_id" value="<?php e($list->get("id")); ?>" />
    <fieldset>
        <legend><?php e(t('search', 'common')); ?></legend>

        <label for="optin"><?php e(t('Filter', 'common')); ?>:
            <select name="optin" id="optin">
                <option value="1" <?php if($subscriber->getDBQuery()->getFilter('optin') == 1) echo 'selected="selected"'; ?> ><?php e(t('Opted in')); ?></option>
                <option value="0" <?php if($subscriber->getDBQuery()->getFilter('optin') == 0) echo 'selected="selected"'; ?> ><?php e(t('Not opted in')); ?></option>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('go', 'common')); ?>" />
        </span>
    </fieldset>
</form>

<?php if (count($subscribers) == 0): ?>
    <p>Der er ikke tilføjet nogen modtager endnu.</p>
<?php else: ?>



    <?php echo $subscriber->getDBQuery()->display('character'); ?>
<table class="stripe">
    <caption>Breve</caption>
    <thead>
    <tr>
        <th>Navn</th>
        <th>E-mail</th>
        <th>Tilmeldt</th>
        <th>Optin</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($subscribers AS $s): ?>
    <tr>
        <td><?php e($s['contact_name']); ?></td>
        <td><?php e($s['contact_email']); ?></td>
        <td><?php e($s['dk_date_submitted']); ?></td>
        <td>
            <?php if ($s['optin'] == 0 and $s['date_optin_email_sent'] < date('Y-m-d', time() - 60 * 60 * 24 * 3)): ?>
                <a href="<?php e($_SERVER['PHP_SELF'] . '?list_id='.$list->get('id')); ?>&amp;id=<?php e($s['id']); ?>&amp;remind=true&amp;use_stored=true"><?php e(t('Remind')); ?></a>
            <?php elseif ($s['optin'] == 0): ?>
                <?php e(t('Not opted in')); ?>
            <?php elseif ($s['optin'] == 1): ?>
                <?php e(t('Opted in')); ?>
            <?php endif; ?>
        </td>
        <td>
            <a class="delete" href="subscribers.php?delete=<?php e($s['id']); ?>&amp;list_id=<?php e($list->get('id')); ?>" title="Dette sletter modtageren">Slet</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <?php echo $subscriber->getDBQuery()->display('paging'); ?>
<?php endif; ?>