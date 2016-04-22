<h1><?php e(t('Users')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <?php if ($context->query('intranet_id') != 0) : ?>
        <li><a href="<?php e(url(null, array('create', 'intranet_id' => $context->getIntranet()->get("id")))); ?>">Opret bruger</a></li>
        <li><a href="<?php e(url(null, array('intranet_id' => $context->getIntranet()->get("id"), 'not_in_intranet' => 1))); ?>">Tilføj eksisterende bruger</a></li>
    <?php endif; ?>
</ul>

<form method="get" action="<?php e(url(null)); ?>">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>
        <label><?php e(t('search text')); ?>:
            <input type="text" name="text" value="<?php e($context->getUser()->getDBQuery($context->getKernel())->getFilter("text")); ?>" />
        </label>
        <span><input type="submit" name="search" value="<?php e(t('search')); ?>" /></span>
    </fieldset>
</form>

<?php echo $context->getUser()->getDBQuery($context->getKernel())->display('character'); ?>

<table>
<thead>
    <tr>
        <?php if ($context->isAddUserTrue()) : ?>
        <th></th>
        <?php endif; ?>
        <th></th>
        <th>Navn</th>
        <th>E-mail</th>
        <th></th>
    </tr>
</thead>
<tbody>
    <?php
    foreach ($context->getUsers() as $user) {
        ?>
        <tr>
            <?php if ($context->isAddUserTrue()) : ?>
            <td><a href="<?php e(url(null, array('add_user_id' => $user["id"]))); ?>"><?php e(t('add')); ?></a></td>
            <?php endif; ?>
            <?php
            if ($user["name"] == '') {
                $user["name"] = '['.t('not filled in').']';
            }
            ?>
            <td><img style="border: none;" src="<?php e('http://www.gravatar.com/avatar/'.md5($user['email']).'?s=20&d=&d='.NET_SCHEME . NET_HOST . url('/images/icons/gravatar.png')); ?>" height="20" width="20" /></td>
            <td><a href="<?php e(url($user["id"])); ?>"><?php e($user["name"]); ?></a></td>
            <td><?php e($user["email"]); ?></td>
            <td class="buttons">
                <a href="<?php e(url($user["id"], array('edit'))); ?>" class="edit">Ret</a>
                <?php /*
                <?php if (isset($)$intranet->get('id') > 0) { ?>
                <a href="user_permission.php?id=<?php e($user["id"]); ?>&amp;intranet_id=<?php e($intranet->get('id')); ?>"><?php e(t('permissions')); ?></a>
                <?php } ?>
				*/ ?>
            </td>
        </tr>
        <?php
    }
    ?>
</tbody>
</table>

<?php echo $context->getUser()->getDBQuery($context->getKernel())->display('paging'); ?>
