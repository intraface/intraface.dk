<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */
require('../../include_first.php');

$module = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');

$intranetmaintenance = new IntranetMaintenance($kernel);
$intranetmaintenance->createDBQuery();

if(isset($_GET["search"])) {

    if(isset($_GET["text"]) && $_GET["text"] != "") {
        $intranetmaintenance->dbquery->setFilter("text", $_GET["text"]);
    }
}
elseif(isset($_GET['character'])) {
    $intranetmaintenance->dbquery->useCharacter();
}

$intranetmaintenance->dbquery->defineCharacter('character', 'name');
$intranetmaintenance->dbquery->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$intranetmaintenance->dbquery->storeResult("use_stored", "intranetmainenance_intranet", "toplevel");
$intranets = $intranetmaintenance->getList();


$page = new Page($kernel);
$page->start($translation->get('intranets'));

?>

<h1><?php echo $translation->get('intranets'); ?></h1>

<ul class="options">
    <li><a href="intranet_edit.php"><?php echo $translation->get('create', 'common'); ?></a></li>
    <li><a href="users.php"><?php echo $translation->get('users'); ?></a></li>
</ul>

<form method="get" action="index.php">
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('search')); ?></legend>
        <label><?php echo safeToHtml($translation->get('search text')); ?>:
            <input type="text" name="text" value="<?php echo $intranetmaintenance->dbquery->getFilter("text"); ?>" />
        </label>
        <span><input type="submit" name="search" value="<?php echo safeToHtml($translation->get('search')); ?>" /></span>
    </fieldset>
</form>


<?php echo $intranetmaintenance->dbquery->display('character'); ?>


<table>
<thead>
    <tr>
        <th><?php echo $translation->get('name'); ?></th>
        <th>&nbsp;</th>
    </tr>
</thead>
<tbody>
    <?php
    for($i = 0; $i < count($intranets); $i++) {
        ?>
        <tr>
            <td><a href="intranet.php?id=<?php echo intval($intranets[$i]["id"]); ?>"><?php echo safeToHtml($intranets[$i]["name"]); ?></a></td>
            <td class="buttons">
                <a href="intranet_edit.php?id=<?php print($intranets[$i]["id"]); ?>"><?php echo $translation->get('edit', 'common'); ?></a>
            </td>
        </tr>
        <?php
    }
    ?>
</tbody>
</table>

<?php echo $intranetmaintenance->dbquery->display('paging'); ?>

<?php
$page->end();
?>
