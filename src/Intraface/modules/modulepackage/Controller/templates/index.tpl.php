<h1><?php e(t('your account')); ?></h1>

<?php echo $modulepackagemanager->error->view(); ?>

<div class="message">
    <?php if ($context->query('status') == 'success') : ?>
        <?php
        // TODO: This is not really a good text
        ?>
        <h3><?php e(t('success!')); ?></h3>
        <p><?php e(t('if everything went as it should, you can see your packages below, and you should be able to use them now.')); ?></p>
    <?php else : ?>
        <p><?php e(t('on this page you have an overview of your intraface account')); ?></p>
    <?php endif; ?>
</div>

<?php
if (count($packages) > 0) {
    ?>
    <h2><?php e(t('your subscription')); ?></h2>
    <table class="stribe">
        <caption><?php e(t('modulepackages')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('modulepackage')); ?></th>
                <th><?php e(t('start date')); ?></th>
                <th><?php e(t('end date')); ?></th>
                <th><?php e(t('status')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($packages as $package) : ?>
            <tr>
                <td><?php e($package['plan'] . ' '. $package['group']); ?></td>
                <td><?php e($package['dk_start_date']); ?></td>
                <td><?php e($package['dk_end_date']); ?></td>
                <td><?php e(t($package['status'])); ?></td>
                <td><a href="<?php e(url(null, array('unsubscribe_id' => $package['id']))); ?>" class="delete"><?php e(t('unsubscribe')); ?></a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}
?>

<h2><?php e(t('subscribe to new package')); ?></h2>

<?php
$modulepackage = new Intraface_modules_modulepackage_ModulePackage;
$plans = $modulepackage->getPlans();
$groups = $modulepackage->getGroups();
$modulepackage->getDBQuery($context->getKernel());
$packages = $modulepackage->getList('matrix');
?>

<table class="stribe">
    <thead>
        <tr>
            <th><?php e(t('select your package')); ?></th>
            <?php foreach ($plans as $plan) : ?>
                <th style="width: <?php echo floor(100/(2 + count($plans))); ?>%;"><?php e(t($plan['plan'])); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // we make sure it is arrays to avoid errors.
        settype($groups, 'array');
        settype($plans, 'array');

        foreach ($groups as $group) { ?>
           <tr>
            <th style="vertical-align: top;">
            <strong><?php e(t($group['group'])); ?></strong>
            <?php
            if (isset($plans[0]['id']) && isset($packages[$group['id']][$plans[0]['id']]) && is_array($packages[$group['id']][$plans[0]['id']])) {
                $modules = $packages[$group['id']][$plans[0]['id']]['modules'];
            } else {
                $modules = array();
            }
            $row_modules = array();
            if (is_array($modules) && count($modules) > 0) { ?>
                <div>
                <?php
                echo t('gives you access to: <br /> - ');
                for ($j = 0, $max = count($modules); $j < $max; $j++) {
                    if ($j != 0) {
                        echo ', ';
                    }
                    e(t($modules[$j]['module']));
                    $row_modules[] = $modules[$j]['module'];
                } ?>
                </div>
                <?php
            }
            ?>
            </th>
            <?php
            foreach ($plans as $plan) { ?>
                <td style="vertical-align: bottom;">
                <?php if (isset($packages[$group['id']][$plan['id']]) && is_array($packages[$group['id']][$plan['id']])) {
                    $modules = array();
                    $limiters = array();
                    if (isset($packages[$group['id']][$plan['id']]['modules']) && is_array($packages[$group['id']][$plan['id']]['modules'])) {
                        foreach ($packages[$group['id']][$plan['id']]['modules'] as $module) {
                            $modules[] = $module['module'];
                            if (is_array($module['limiters']) && count($module['limiters']) > 0) {
                                $limiters = array_merge($limiters, $module['limiters']);
                            }
                        }
                    }

                    $display_modules = array_diff($modules, $row_modules);
                    if (is_array($display_modules) && count($display_modules) > 0) { ?>
                        <p><?php e(t('plus the modules')); ?>: <br />
                        <?php echo implode(', ', $display_modules); ?>
                        </p>
                    <?php
                    }

                    if (is_array($limiters) && count($limiters) > 0) { ?>
                        <p><?php e(t('gives you')); ?>:

                        <?php foreach ($limiters as $limiter) { ?>
                            <br /><?php e(t($limiter['description']).' ');
                            if (isset($limiter['limit_readable'])) {
                                e($limiter['limit_readable']);
                            } else {
                                e($limiter['limit']);
                            }
} ?>
                        </p>
                    <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         }

                    if (is_array($packages[$group['id']][$plan['id']]['product']) && count($packages[$group['id']][$plan['id']]['product']) > 0) { ?>
                        <p> DKK <?php e($packages[$group['id']][$plan['id']]['product']['price_incl_vat'].' '.t('per').' '.t($packages[$group['id']][$plan['id']]['product']['unit']['singular'])); ?></p>
                    <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         } ?>

                    <a href="<?php e(url('package/' . $packages[$group['id']][$plan['id']]['id'])); ?>"><?php e(t('choose')); ?></a>

                <?php } ?>
                </td>
                <?php
            }
        }
        ?>
    </tbody>
</table>
