<?php
$module_msg = $context->getModuleMsg();
?>
<h1><?php e(t('Modules')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" value="put" name="_method" />
    <input type="submit" value="<?php e(t('Register modules')); ?>" />
</form>

<?php echo $context->getModuleMaintenance()->error->view(); ?>

<table>
    <thead>
        <tr>
            <th><?php e(t('Name')); ?></th>
            <th><?php e(t('Sub access')); ?></th>
            <th><?php e(t('Show in menu')); ?></th>
            <th><?php e(t('Menu / frontpage index')); ?></th>
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
