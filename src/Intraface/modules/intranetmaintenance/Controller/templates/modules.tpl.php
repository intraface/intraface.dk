<?php
$module_msg = $context->getModuleMsg();
?>
<h1><?php e(__('Modules')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(__('Close')); ?></a></li>
    <li><a href="<?php e(url(null, array('do' => 'register'))); ?>"><?php e(__('Register modules')); ?></a></li>
</ul>

<?php echo $context->getModuleMaintenance()->error->view(); ?>

<table>
    <thead>
        <tr>
            <th>Navn</th>
            <th>Subaccess</th>
            <th>Menupunkt</th>
            <th>Menu/Frontpage index</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($context->getModules() as $module) {
        ?>
        <tr>
            <td>
                <?php e(t($module["name"])); ?>
                <?php if (isset($module_msg[$module["name"]])) print("<br /><span class=\"red\">".$module_msg[$module["name"]]."</span>"); ?>
            </td>
            <td>
                <?php
                if (!empty($module["sub_access"])) {
                    for ($j = 0, $maxj = count($module["sub_access"]); $j < $maxj; $j++) {
                        e($module["sub_access"][$j]["description"]);
                        echo "<br />";
                    }
                }
                ?>
            </td>
            <td><?php ($module["show_menu"] == 1) ? e("Ja") : e("Nej"); ?></td>
            <td><?php e($module['menu_index'].' / '.$module['frontpage_index']); ?></td>

            <td class="buttons">
                <?php /*
                <a href="modules.php?moveup=<?php e($module["id"]); ?>">[Flyt op]</a>
                <a href="modules.php?movedown=<?php e($module["id"]); ?>">[Flyt ned]</a>
                */ ?>
            </td>
        </tr>
        <?php
    }
    ?>
</tbody>
</table>

<?php
if (isset($module_msg[0])) {
    print("<p class=\"red\">".$module_msg['update']."</p>");
}
?>
