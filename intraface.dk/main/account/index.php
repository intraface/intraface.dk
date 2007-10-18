<?php
require('../../include_first.php');
require_once('Intraface/ModulePackage.php');
require_once('Intraface/ModulePackage/Manager.php');

// temp test
// require('Intraface/ModulePackage/AccessUpdate.php');
// $access_update = new Intraface_ModulePackage_AccessUpdate();
// $access_update->run($kernel->intranet->get('id'));

if(isset($_GET['unsubscribe_id']) && intval($_GET['unsubscribe_id']) != 0) {
    $modulepackagemanager = new Intraface_ModulePackage_Manager($kernel->intranet, (int)$_GET['unsubscribe_id']);
    if($modulepackagemanager->get('id') != 0) {
        if($modulepackagemanager->get('status') == 'created') {
            $modulepackagemanager->delete();
        }
        elseif($modulepackagemanager->get('status') == 'active') {
            $modulepackagemanager->terminate();
            
            require_once('Intraface/ModulePackage/AccessUpdate.php');
            $access_update = new Intraface_ModulePackage_AccessUpdate();
            $access_update->run($kernel->intranet->get('id'));
            $kernel->user->clearCachedPermission();
            
        }
        else {
            $modulepackagemanager->error->set('it is not possible to unsubscribe module packages which is not either created or active');
        }
    }
}

$translation = $kernel->getTranslation('modulepackage');

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('account')));
?>
<h1><?php echo safeToHtml($translation->get('account')); ?></h1>

<?php if(isset($modulepackagemanager)) $modulepackagemanager->error->view(); ?>

<div class="message">
    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <?php
        // TODO: This is not really a good text
        ?>
        <h3><?php echo safeToHtml($translation->get('success!')); ?></h3>
        <p><?php echo safeToHtml($translation->get('if everything went as it should, you can see your packages below, and you should be able to use them now.')); ?></p>
    <?php else: ?>
        <p><?php echo safeToHtml($translation->get('on this page you have an overview of your intraface account')); ?></p>
    <?php endif; ?>
</div>

<?php
$modulepackagemanager = new Intraface_ModulePackage_Manager($kernel->intranet);
$modulepackagemanager->createDBQuery($kernel);
$modulepackagemanager->dbquery->setFilter('status', 'created_and_active');
$packages = $modulepackagemanager->getList();
    
if(count($packages) > 0) {
    ?>
    <h2><?php echo safeToHtml($translation->get('your subscription')); ?></h2>
    <table class="stribe">
        <caption><?php echo safeTohtml($translation->get('modulepackages')); ?></caption>
        <thead>
            <tr>
                <th><?php echo safeToHtml($translation->get('modulepackage')); ?></th>
                <th><?php echo safeToHtml($translation->get('start date')); ?></th>
                <th><?php echo safeToHtml($translation->get('end date')); ?></th>
                <th><?php echo safeToHtml($translation->get('status')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($packages AS $package): ?>
            <tr>
                <td><?php echo safeToHtml($translation->get($package['plan']).' '.$translation->get($package['group'])); ?></td>
                <td><?php echo safeToHtml($package['dk_start_date']); ?></td>
                <td><?php echo safeToHtml($package['dk_end_date']); ?></td>
                <td><?php echo safeToHtml($translation->get($package['status'])); ?></td>
                <td><a href="index.php?unsubscribe_id=<?php echo intval($package['id']); ?>" class="delete"><?php echo safeToHtml($translation->get('unsubscribe')); ?></a></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}
?>

<h2><?php echo safeToHtml($translation->get('subscribe to new package')); ?></h2>

<?php
$modulepackage = new Intraface_ModulePackage;
$plans = $modulepackage->getPlans();
$groups = $modulepackage->getGroups();
$modulepackage->createDBQuery($kernel);
$packages = $modulepackage->getList('matrix');
?>

<table class="stribe">
    <thead>
        <tr>
            <th><?php echo safeToHtml($translation->get('select your package')); ?></th>
            <?php foreach($plans AS $plan): ?>
                <th style="width: <?php echo floor(100/(2 + count($plans))); ?>%;"><?php echo safeToHtml($translation->get($plan['plan'])); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php 
        // we make sure it is arrays to avoid errors. 
        settype($groups, 'array');
        settype($plans, 'array');
        
        foreach($groups AS $group) {
            
            echo '<tr>';
            echo '<th style="vertical-align: top;">';
            echo '<strong>'.safeToHtml($translation->get($group['group'])).'</strong>';
            if(isset($plans[0]['id']) && isset($packages[$group['id']][$plans[0]['id']]) && is_array($packages[$group['id']][$plans[0]['id']])) {
                $modules = $packages[$group['id']][$plans[0]['id']]['modules'];
            }
            else {
                $modules = array();
            }
            $row_modules = array();
            if(is_array($modules) && count($modules) > 0) {
                echo '<div>';
                echo $translation->get('gives you access to: <br /> - ');
                for($j = 0, $max = count($modules); $j < $max; $j++) {
                    if($j != 0) {
                        echo ', ';
                    }
                    echo $translation->get($modules[$j]['module']);
                    $row_modules[] = $modules[$j]['module'];
                }
                echo '</div>';        
            }
            echo '</th>'; 
            
            foreach($plans AS $plan) {
                echo '<td style="vertical-align: bottom;">';
                if(isset($packages[$group['id']][$plan['id']]) && is_array($packages[$group['id']][$plan['id']])) {
                    
                    $modules = array();
                    $limiters = array();
                    if(isset($packages[$group['id']][$plan['id']]['modules']) && is_array($packages[$group['id']][$plan['id']]['modules'])) {
                        foreach($packages[$group['id']][$plan['id']]['modules'] AS $module) {
                            $modules[] = $module['module'];
                            if(is_array($module['limiters']) && count($module['limiters']) > 0) {
                                $limiters = array_merge($limiters, $module['limiters']); 
                            } 
                        }
                    }
                    
                    $display_modules = array_diff($modules, $row_modules);
                    if(is_array($display_modules) && count($display_modules) > 0) {
                        echo '<p>'.safeToHtml($translation->get('plus the modules')).': <br /> ';
                        echo implode(', ', $display_modules);
                        echo '</p>';
                    }
                    
                    if(is_array($limiters) && count($limiters) > 0) {
                        echo '<p>'.safeToHtml($translation->get('gives you')).': ';
                        
                        foreach($limiters AS $limiter) {
                            echo '<br />'.safeToHtml($translation->get($limiter['description']).' ');
                            if(isset($limiter['limit_readable'])) {
                                print safeToHtml($limiter['limit_readable']);
                            }
                            else {
                                print safeToHtml($limiter['limit']);
                            }
                        }
                        echo '</p>';
                    }
                    
                    if(is_array($packages[$group['id']][$plan['id']]['product']) && count($packages[$group['id']][$plan['id']]['product']) > 0) {
                        echo '<p> DKK '.safeToHtml($packages[$group['id']][$plan['id']]['product']['price_incl_vat']).' '.$translation->get('per').' '.$translation->get($packages[$group['id']][$plan['id']]['product']['unit_declensions']['singular']).'</p>';
                    }
                    
                    echo '<a href="add_package.php?id='.$packages[$group['id']][$plan['id']]['id'].'">'.$translation->get('choose', 'common').'</a>';
                    
                }
                echo '</td>'; 
            }
        }
        ?>
    </tbody>
</table>

<?php
$page->end();
?>