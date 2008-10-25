<?php
require '../../include_first.php';

$primary_module = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation("intranetmaintenance");

$module = new ModuleMaintenance;
$modules = $module->getList();

if (isset($_GET["do"]) && $_GET["do"] == "register") {
    $module_msg = $module->register();
    $kernel->user->clearCachedPermission(); // Sørger for at permissions bliver reloaded.
    $modules = $module->getList();
} else {
    $module_msg = array();
}

$page = new Intraface_Page($kernel);
$page->start("Moduler");
?>

<h1>Moduler</h1>

<ul class="options">
    <li><a href="modules.php?do=register"><?php e($translation->get('register modules')); ?></a></li>
</ul>

<?php echo $module->error->view(); ?>

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
    for ($i = 0, $max = count($modules); $i < $max; $i++) {
        ?>
        <tr>
            <td>
                <?php e(t($modules[$i]["name"])); ?>
                <?php if (isset($module_msg[$modules[$i]["name"]])) print("<br /><span class=\"red\">".$module_msg[$modules[$i]["name"]]."</span>"); ?>
            </td>
            <td>
                <?php
                if (!empty($modules[$i]["sub_access"])) {
                    for ($j = 0, $maxj = count($modules[$i]["sub_access"]); $j < $maxj; $j++) {
                        e($modules[$i]["sub_access"][$j]["description"]);
                        echo "<br />";
                    }
                }
                ?>
            </td>
            <td><?php ($modules[$i]["show_menu"] == 1) ? e("Ja") : e("Nej"); ?></td>
            <td><?php e($modules[$i]['menu_index'].' / '.$modules[$i]['frontpage_index']); ?></td>

            <td class="buttons">
                <?php /*
                <a href="modules.php?moveup=<?php e($modules[$i]["id"]); ?>">[Flyt op]</a>
                <a href="modules.php?movedown=<?php e($modules[$i]["id"]); ?>">[Flyt ned]</a>
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

<?php
$page->end();
?>
