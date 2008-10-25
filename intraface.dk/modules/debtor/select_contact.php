<?php
require '../../include_first.php';
$module = $kernel->module("debtor");
$contact_module = $kernel->useModule('contact');

$error = new Intraface_Error();

if (isset($_POST["search"]) && $_POST["search"] != "") {
    $contact = new Contact($kernel);

    $contact->getDBQuery()->setFilter("search", $_POST["search"]);
    $contacts = $contact->getList($_POST["search"]);

    $options = '';

    if (isAjax()) {
        header("Content-type: text/xml");
        print("<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n");
        print("<searchresult>\n");
        for ($i = 0, $max = count($contacts); $i < $max; $i++) {
            print("<contact>\n");
            print("<id>".htmlentities($contacts[$i]["id"])."</id>\n");
            print("<name>".htmlentities($contacts[$i]["name"])."</name>\n");
            print("</contact>\n");
        }
        print("</searchresult>");

        exit;
    } else {
        for ($i = 0, $max = count($contacts); $i < $max; $i++) {
            $options .= '<option value="'.$contacts[$i]["id"].'">';
            $options .= $contacts[$i]["name"];
            $options .= "</option>\n";
        }
    }

}

if (isset($_POST["go_search"])) {
    /*
    if ($_POST["type"] == "reminder" && $kernel->user->hasModuleAccess("invoice")) {
        header("Location: reminder_edit.php?contact_id=".$_POST["contact"]."");
        exti;
    }
    */
    if (($_POST["type"] == "quotation" || $_POST["type"] == "order" || $_POST["type"] == "invoice") && $kernel->user->hasModuleAccess($_POST["type"])) {
        if (empty($_POST['contact']) OR !is_numeric($_POST['contact'])) {
            $error->set('Du skal vælge en kontakt');
        }
        if (!$error->isError()) {
            header("Location: edit.php?type=".$_POST["type"]."&contact_id=".$_POST["contact"]."");
            exit;
        }
        else {
            $value = $_POST;
            $type = $_POST['type'];
        }
    }
    else {
        trigger_error("Ugyldig type", E_USER_ERROR);
    }
}
elseif (isset($_POST["new_contact"])) {
    if ($_POST["type"] == "quotation" || $_POST["type"] == "order" || $_POST["type"] == "invoice") {

        $redirect = new Intraface_Redirect($kernel);
        // @todo make sure that you can have an regret destination
        $url = $redirect->setDestination($contact_module->getPath().'contact_edit.php', $module->getPath().'edit.php?type='.$_POST['type'], $module->getPath().'select_contact.php?type='.$_POST['type']); // contact_id sættes på fra contact_edit
        $redirect->askParameter('contact_id');
        header('Location: ' . $url);
        exit;

        /*
        header("location: /modules/contact/contact_edit.php?go_to=".$_POST["type"]);
        exit;
        */
    }
    else {
        trigger_error("Ugyldig type", E_USER_ERROR);
    }
}
else {

    if (isset($_GET["type"])) {

        if ($_GET["type"] == "reminder" && $kernel->user->hasModuleAccess("invoice")) {
            $type = "reminder";
        }
        elseif (($_GET["type"] == "quotation" || $_GET["type"] == "order" || $_GET["type"] == "invoice") && $kernel->user->hasModuleAccess($_GET["type"])) {
            $type = $_GET["type"];
        }
        else {
            $type = "";
        }
    }
    else {
        $type = "";
    }
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('global', 'XMLHttp.js');
$page->includeJavascript('module', 'select_contact.js');
$page->start('Opret ny');
?>

<h1>Opret tilbud, ordre eller faktura</h1>

<?php echo $error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post" class="clearfix" id="new">
<table>
<thead>
<tr>
    <th>Hvad?</th>
    <th>Til?</th>
    <th></th>
</tr>
</thead>
<tbody>
<tr>
  <td>
  <label id="type_label">Opret
      <select name="type" id="type_select">
    <option value="">Vælg</option>
        <option value="quotation" <?php if ($type == "quotation") print("selected=\"selected\""); ?>>Tilbud</option>
        <option value="order" <?php if ($type == "order") print("selected=\"selected\""); ?>>Ordre</option>
        <option value="invoice" <?php if ($type == "invoice") print("selected=\"selected\""); ?>>Faktura</option>
    </select>
   </label>
 </td>

<td>
<input type="submit" name="new_contact" id="new_contact" value="Ny kontakt" />
<?php $contact = new Contact($kernel); if ($contact->isFilledIn()): ?>
eller
<label>søg
        <input type="text" name="search" id="search" value="<?php if (!empty($_GET['search'])) e($_GET['search']); ?>" />
</label>

        <input type="submit" value="Søg" id="search_button" />
        <select name="contact" size="4" id="contact_select" style="width:300px;"><?php if (isset($options)) e($options); ?></select>
<?php endif; ?>
  </td>

 <td>
  <input type="submit" name="go_search" value="Opret" />
  </td>
  </tr></tbody>
  </table>

</form>

<?php
$page->end();
?>
