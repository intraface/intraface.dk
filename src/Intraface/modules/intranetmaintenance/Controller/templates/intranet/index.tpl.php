<h1><?php e(t('Intranets')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a href="<?php e(url(null) . '?new'); ?>"><?php e(t('create')); ?></a></li>
    <li><a href="<?php e(url('../user')); ?>"><?php e(t('users')); ?></a></li>
</ul>

<form method="get" action="<?php e(url(null)); ?>">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>
        <label><?php e(t('search text')); ?>:
            <input type="text" name="text" value="<?php e($context->getIntranetmaintenance()->getDBQuery($context->getKernel())->getFilter("text")); ?>" />
        </label>
        <span><input type="submit" name="search" value="<?php e(t('search')); ?>" /></span>
    </fieldset>
</form>

<?php echo $intranet_maintenance->getDBQuery($context->getKernel())->display('character'); ?>

<table>
<thead>
    <tr>
        <th><?php e(t('Name')); ?></th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($intranets as $intranet) : ?>
        <tr>
            <td><a href="<?php e(url($intranet["id"])); ?>"><?php e($intranet["name"]); ?></a></td>
            <td class="buttons">
                <a href="<?php e(url($intranet["id"], array('edit'))); ?>"><?php e(t('edit')); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
</tbody>
</table>

<?php echo $intranet_maintenance->getDBQuery($context->getKernel())->display('paging'); ?>
