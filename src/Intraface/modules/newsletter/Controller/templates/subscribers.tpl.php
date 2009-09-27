<h1><?php e(t('Subscribers to the list')); ?> <?php e($context->getList()->get('title')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url(null, array('add_contact' => true))); ?>"><?php e(t('Add contact')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php echo $context->getSubscriber()->error->view(); ?>

<form action="<?php e(url(null)); ?>" method="get" class="search-filter">
    <input type="hidden" name="list_id" value="<?php e($context->getList()->get("id")); ?>" />
    <fieldset>
        <legend><?php e(t('Search', 'common')); ?></legend>

        <label for="optin"><?php e(t('Filter', 'common')); ?>:
            <select name="optin" id="optin">
                <option value="1" <?php if($context->getSubscriber()->getDBQuery()->getFilter('optin') == 1) echo 'selected="selected"'; ?> ><?php e(t('Opted in')); ?></option>
                <option value="0" <?php if($context->getSubscriber()->getDBQuery()->getFilter('optin') == 0) echo 'selected="selected"'; ?> ><?php e(t('Not opted in')); ?></option>
            </select>
        </label>
        <span>
            <input type="submit" value="<?php e(t('go', 'common')); ?>" />
        </span>
    </fieldset>
</form>

<?php if (count($context->getSubscribers()) == 0): ?>
    <p><?php e(t('No subscribers added yet.')); ?></p>
<?php else: ?>

    <?php echo $context->getSubscriber()->getDBQuery()->display('character'); ?>
<table class="stripe">
    <caption><?php e(t('Letters')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('Name')); ?></th>
        <th><?php e(t('Email')); ?></th>
        <th><?php e(t('Subscribed')); ?></th>
        <th><?php e(t('Optin')); ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($context->getSubscribers() AS $s): ?>
    <tr>
        <td><?php e($s['contact_name']); ?></td>
        <td><?php e($s['contact_email']); ?></td>
        <td><?php e($s['dk_date_submitted']); ?></td>
        <td>
            <?php if ($s['optin'] == 0 and $s['date_optin_email_sent'] < date('Y-m-d', time() - 60 * 60 * 24 * 3)): ?>
                <a href="<?php e($_SERVER['PHP_SELF'] . '?list_id='.$context->getList()->get('id')); ?>&amp;id=<?php e($s['id']); ?>&amp;remind=true&amp;use_stored=true"><?php e(t('Remind')); ?></a>
            <?php elseif ($s['optin'] == 0): ?>
                <?php e(t('Not opted in')); ?>
            <?php elseif ($s['optin'] == 1): ?>
                <?php e(t('Opted in')); ?>
            <?php endif; ?>
        </td>
        <td>
            <a class="delete" href="subscribers.php?delete=<?php e($s['id']); ?>&amp;list_id=<?php e($context->getList()->get('id')); ?>"><?php e(t('Delete')); ?></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <?php echo $context->getSubscriber()->getDBQuery()->display('paging'); ?>
<?php endif; ?>