<h1><?php e(__('Intranets')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url(null) . '?new'); ?>"><?php e(__('create', 'common')); ?></a></li>
    <li><a href="<?php e(url('../user')); ?>"><?php e(__('users')); ?></a></li>
</ul>

<form method="get" action="<?php e(url(null)); ?>">
    <fieldset>
        <legend><?php e(__('search')); ?></legend>
        <label><?php e(__('search text')); ?>:
            <input type="text" name="text" value="<?php e($context->getIntranetmaintenance()->getDBQuery($context->getKernel())->getFilter("text")); ?>" />
        </label>
        <span><input type="submit" name="search" value="<?php e(__('search')); ?>" /></span>
    </fieldset>
</form>

<?php echo $context->getIntranetmaintenance()->getDBQuery($context->getKernel())->display('character'); ?>

<table>
<thead>
    <tr>
        <th><?php e(__('name')); ?></th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($context->getIntranets() as $intranet): ?>
        <tr>
            <td><a href="<?php e(url($intranet["id"])); ?>"><?php e($intranet["name"]); ?></a></td>
            <td class="buttons">
                <a href="<?php e(url($intranet["id"], array('edit'))); ?>"><?php e(__('edit', 'common')); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
</tbody>
</table>

<?php echo $context->getIntranetmaintenance()->getDBQuery($context->getKernel())->display('paging'); ?>
