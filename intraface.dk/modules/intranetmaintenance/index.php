<?php
/**
 * @author Sune Jensen <sj@sunet.dk>
 */
require '../../include_first.php';

$module = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');

$intranetmaintenance = new IntranetMaintenance();

if(isset($_GET["search"])) {
    if(isset($_GET["text"]) && $_GET["text"] != "") {
        $intranetmaintenance->getDBQuery($kernel)->setFilter("text", $_GET["text"]);
    }
} elseif(isset($_GET['character'])) {
    $intranetmaintenance->getDBQuery($kernel)->useCharacter();
}

$intranetmaintenance->getDBQuery($kernel)->defineCharacter('character', 'name');
$intranetmaintenance->getDBQuery($kernel)->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$intranetmaintenance->getDBQuery($kernel)->storeResult("use_stored", "intranetmainenance_intranet", "toplevel");
$intranets = $intranetmaintenance->getList();

$page = new Intraface_Page($kernel);
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
            <input type="text" name="text" value="<?php echo $intranetmaintenance->getDBQuery($kernel)->getFilter("text"); ?>" />
        </label>
        <span><input type="submit" name="search" value="<?php echo safeToHtml($translation->get('search')); ?>" /></span>
    </fieldset>
</form>

<?php echo $intranetmaintenance->getDBQuery($kernel)->display('character'); ?>

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

<?php echo $intranetmaintenance->getDBQuery($kernel)->display('paging'); ?>

<?php
$page->end();
?>