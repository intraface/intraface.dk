<?php
require('../../include_first.php');
require_once('Intraface/tools/Position.php');

$primary_module = $kernel->module("intranetmaintenance");

$translation = $kernel->getTranslation("intranetmaintenance");


$module = new ModuleMaintenance($kernel);
$modules = $module->getList();
/*
if (isset($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
	$position = new Position("module", "", "position", "id"); // Der kan ikke bruges type_id for nøgleord har jo en anden type
	$position->moveUp($_GET['moveup']);
}
elseif (isset($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
	$position = new Position("module", "", "position", "id"); // Der kan ikke bruges type_id for nøgleord har jo en anden type
	$position->moveDown($_GET['movedown']);
}
*/


if(isset($_GET["do"]) && $_GET["do"] == "register") {
	$module_msg = $module->register();
	$kernel->user->clearCachedPermission(); // Sørger for at permissions bliver reloaded.
	$modules = $module->getList();
}
else {
	$module_msg = array();
}

$page = new Page($kernel);
$page->start("Moduler");
?>

<h1>Moduler</h1>

<ul class="options">
	<li><a href="modules.php?do=register"><?php echo $translation->get('register modules'); ?></a></li>
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
	for($i = 0, $max = count($modules); $i < $max; $i++) {
		?>
		<tr>
			<td>
				<?php print($modules[$i]["menu_label"]); ?>
				<?php if(isset($module_msg[$modules[$i]["name"]])) print("<br /><span class=\"red\">".$module_msg[$modules[$i]["name"]]."</span>"); ?>
			</td>
			<td>
				<?php
				if (!empty($modules[$i]["sub_access"])) {
					for($j = 0, $maxj = count($modules[$i]["sub_access"]); $j < $maxj; $j++) {
						print($modules[$i]["sub_access"][$j]["description"]."<br />");
					}
				}
				?>
			</td>
			<td><?php ($modules[$i]["show_menu"] == 1) ? print("Ja") : print("Nej"); ?></td>
			<td><?php echo $modules[$i]['menu_index'].' / '.$modules[$i]['frontpage_index']; ?></td>

			<td class="buttons">
				<?php /*
				<a href="modules.php?moveup=<?php echo $modules[$i]["id"]; ?>">[Flyt op]</a>
				<a href="modules.php?movedown=<?php echo $modules[$i]["id"]; ?>">[Flyt ned]</a>
				*/ ?>
			</td>
		</tr>
		<?php
	}
	?>
</tbody>
</table>

<?php
if(isset($module_msg[0])) {
	print("<p class=\"red\">".$module_msg['update']."</p>");
}
?>

<?php
$page->end();
?>
