<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('debtor');

$mDebtor = $kernel->module('debtor');
$contact_module = $kernel->useModule('contact');
$product_module = $kernel->useModule('product');

if (empty($_GET['id'])) $_GET['id'] = '';
if (empty($_GET['type'])) $_GET['type'] = '';
if (empty($_GET["contact_id"])) $_GET['contact_id'] = '';
if (empty($_GET["status"])) $_GET['status'] = '';

$debtor = Debtor::factory($kernel, intval($_GET["id"]), $_GET["type"]);


if(isset($_GET["action"]) && $_GET["action"] == "delete") {
	$debtor->delete();
}

if(isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0) {
	$debtor->dbquery->setFilter("contact_id", $_GET["contact_id"]);
}

if(isset($_GET["product_id"]) && intval($_GET["product_id"]) != 0) {
	$debtor->dbquery->setFilter("product_id", $_GET["product_id"]);
}


// søgning
	// if(isset($_POST['submit'])
	if(isset($_GET["text"]) && $_GET["text"] != "") {
		$debtor->dbquery->setFilter("text", $_GET["text"]);
	}

	if(isset($_GET["from_date"]) && $_GET["from_date"] != "") {
		$debtor->dbquery->setFilter("from_date", $_GET["from_date"]);
	}

	if(isset($_GET["to_date"]) && $_GET["to_date"] != "") {
		$debtor->dbquery->setFilter("to_date", $_GET["to_date"]);
	}

	if($debtor->dbquery->checkFilter("contact_id")) {
		$debtor->dbquery->setFilter("status", "-1");
	}
	elseif(isset($_GET["status"]) && $_GET['status'] != '') {
		$debtor->dbquery->setFilter("status", $_GET["status"]);
	}
	else {
		$debtor->dbquery->setFilter("status", "-2");
	}

	if(!empty($_GET['not_stated']) AND $_GET['not_stated'] == 'true') {
		$debtor->dbquery->setFilter("not_stated", true);
	}

// er der ikke noget galt herunder (LO) - brude det ikke være order der bliver sat?
if(isset($_GET['sorting']) && $_GET['sorting'] != 0) {
	$debtor->dbquery->setFilter("sorting", $_GET['sorting']);
}

$debtor->dbquery->usePaging("paging", $kernel->setting->get('user', 'rows_pr_page'));
$debtor->dbquery->storeResult("use_stored", $debtor->get("type"), "toplevel");
$debtor->dbquery->setExtraUri('&amp;type='.$debtor->get("type"));



$posts = $debtor->getList();

if(intval($debtor->dbquery->getFilter('product_id')) != 0) {
	$product = new Product($kernel, $debtor->dbquery->getFilter('product_id'));
}

if(intval($debtor->dbquery->getFilter('contact_id')) != 0) {
	$contact = new Contact($kernel, $debtor->dbquery->getFilter('contact_id'));
}

$page = new Intraface_Page($kernel);
$page->includeJavascript('module', 'list.js');
$page->start(safeToHtml($translation->get($debtor->get('type').'s')));

?>

<h1>
	<?php
		print($translation->get($debtor->get("type").'s'));
		if (!empty($contact) AND is_object($contact) && $contact->address->get('name') != '') {
			echo ': ' . safeToHtml($contact->address->get('name'));
		}

		if (!empty($product) AND is_object($product) && $product->get('name') != '') {
			echo ' med produkt: ' . safeToHtml($product->get('name'));
		}
	?>
</h1>

<?php if($kernel->intranet->address->get('id') == 0): ?>
	<p>Du mangler at udfylde adresse til dit intranet. Det skal du gøre, før du kan oprette en <?php print(safeToHtml(strtolower($translation->get($debtor->get('type'))))); ?>.
	<?php if($kernel->user->hasModuleAccess('administration')): ?>
		<?php
		$module_administration = $kernel->useModule('administration');
		?>
		<a href="<?php echo safeToHtml($module_administration->getPath().'intranet_edit.php'); ?>">Udfyld adresse</a>.</p>
	<?php else: ?>
		Du har ikke adgang til at rette adresseoplysningerne, det må du bede din administrator om at gøre.</p>
	<?php endif; ?>

<?php elseif (!$debtor->isFilledIn()): ?>

	<p>Du har endnu ikke oprettet nogen. <a href="select_contact.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a>.</p>

<?php else: ?>

<ul class="options">
	<?php if(!empty($contact) AND is_object($contact) AND $debtor->get("type") != "credit_note"): ?>
		<li><a href="edit.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>&amp;contact_id=<?php print(intval($contact->get("id"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a></li>
		<li><a href="<?php echo $contact_module->getPath(); ?>contact.php?id=<?php echo intval($contact->get('id')); ?>">Vis kontakten</a>
	<?php else: ?>
		<?php if(!empty($_GET['product_id'])): ?>
			<li><a href="<?php echo $product_module->getPath(); ?>product.php?id=<?php echo intval($product->get('id')); ?>">Vis produktet</a>
		<?php endif; ?>
		<li><a href="select_contact.php?type=<?php print(safeToHtml($debtor->get("type"))); ?>"><?php print(safeToHtml($translation->get('create '.$debtor->get('type')))); ?></a></li>
	<?php endif; ?>
	<li><a class="excel" href="export_excel.php?type=<?php print(safeToHtml($debtor->get('type'))); ?>&amp;use_stored=true">Exporter liste til Excel</a></li>
</ul>


<?php echo $debtor->error->view(); ?>

<?php if(!isset($_GET['$contact_id'])): ?>

	<fieldset class="hide_on_print">
		<legend>Avanceret søgning</legend>
		<form method="get" action="list.php">
		<label>Tekst
			<input type="text" name="text" value="<?php echo safeToHtml($debtor->dbquery->getFilter("text")); ?>" />
		</label>
		<label>Status
		<select name="status">
			<option value="-1">Alle</option>
			<option value="-2"<?php if ($debtor->dbquery->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
			<?php if($debtor->get("type") == "invoice"): ?>
			<option value="-3"<?php if ($debtor->dbquery->getFilter("status") == -3) echo ' selected="selected"';?>>Afskrevet</option>
			<?php endif; ?>
			<option value="0"<?php if ($debtor->dbquery->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
			<option value="1"<?php if ($debtor->dbquery->getFilter("status") == 1) echo ' selected="selected"';?>>Sendt</option>
			<option value="2"<?php if ($debtor->dbquery->getFilter("status") == 2) echo ' selected="selected"';?>>Afsluttet</option>
			<option value="3"<?php if ($debtor->dbquery->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
		</select>
		</label>
		<!-- sortering bør være placeret ved at man klikker på en overskrift i stedet - og så bør man kunne sortere på det hele -->
		<label>Sortering
		<select name="sorting">
			<option value="0"<?php if ($debtor->dbquery->getFilter("sorting") == 0) echo ' selected="selected"';?>>Fakturanummer faldende</option>
			<option value="1"<?php if ($debtor->dbquery->getFilter("sorting") == 1) echo ' selected="selected"';?>>Fakturanummer stigende</option>
			<option value="2"<?php if ($debtor->dbquery->getFilter("sorting") == 2) echo ' selected="selected"';?>>Kontaktnummer</option>
			<option value="3"<?php if ($debtor->dbquery->getFilter("sorting") == 3) echo ' selected="selected"';?>>Kontaktnavn</option>
		</select>
		</label>
		<br />

		<label>Fra dato
			<input type="text" name="from_date" id="date-from" value="<?php print(safeToForm($debtor->dbquery->getFilter("from_date"))); ?>" /> <span id="calender"></span>
		</label>
		<label>Til dato
			<input type="text" name="to_date" value="<?php print(safeToForm($debtor->dbquery->getFilter("to_date"))); ?>" />
		</label>

		<span>
		<input type="hidden" name="type" value="<?php print(safeToForm($debtor->get("type"))); ?>" />
		<input type="hidden" name="contact_id" value="<?php print(intval($debtor->dbquery->getFilter('contact_id'))); ?>" />
		<input type="hidden" name="product_id" value="<?php print(intval($debtor->dbquery->getFilter('product_id'))); ?>" />
		<input type="submit" name="search" value="Find" />
		</span>



		</form>
	</fieldset>

<?php endif; ?>

<?php
$i = 0;
$caption = $translation->get($debtor->get("type").' title');
/*

		<th>Nr.</th>
			<th colspan="2">Kontakt</th>
			<th>Beskrivelse</th>
			<th class="amount">Beløb</th>
			if($debtor->dbquery->getFilter("status") == -3):
				<th class="amount">Afskrevet</th>
			 endif;
			<th>Sendt</th>
			<th> print(safeToHtml($translation->get($debtor->get('type').' due date')));</th>
			<th>&nbsp;</th>
		</tr>
*/

$total = 0;
$due_total = 0;
$sent_total = 0;
$deprication_total = 0;

foreach($posts AS $post) {
	$d[$i]['number'] = $post['number'];
	$contact['number'] = $post['contact']['number'];
	$contact['name'] = $post["name"];
	$contact['url'] = $contact_module->getPath() . 'contact.php?id=' . $post["contact_id"];
	$d[$i]['description'] = $post['description'];
	$d[$i]['url'] = 'view.php?id='.$post['id'];
	$d[$i]['total'] = number_format($post["total"], 2, ",",".");

	if($debtor->dbquery->getFilter("status") == -3) {
		$deprication_total += $posts[$i]["deprication"];
		$d[$i]['depreciation'] = number_format($post["deprication"], 2, ",",".");
	}
	if ($posts[$i]["status"] != "created") {
		$d[$i]['sent'] = $post["dk_date_sent"];
	}
	else {
		$d[$i]['sent'] = 'Nej';
	}

	if($debtor->get('type') == "invoice" && $post['status'] == "sent" && $post['arrears'] != 0) {
		$arrears = " (".number_format($post['arrears'], 2, ",", ".").")";
	}
	else {
		$arrears = "";
	}

	if($post["status"] == "executed" || $post["status"] == "cancelled") {
		$d[$i]['status'] = $translation->get($post["status"]);
	}
	elseif($post["due_date"] < date("Y-m-d")) {
		$d[$i]['status'] = $post["dk_due_date"].$arrears;
	}
	else {
		$d[$i]['status'] = $post["dk_due_date"].$arrears;
	}
	$i++;
}



require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/QuickHtml.php';
require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

// prepare the form and the QuickHtml renderer
$form =& new HTML_QuickForm();
$renderer =& new HTML_QuickForm_Renderer_QuickHtml();
$form->addElement('select', 'action', 'choose', array('delete' => 'Delete'));
$form->addElement('submit', 'submit', 'Save');

$datagrid =& new Structures_DataGrid(2, null);
// by the way there's no need to call setRenderer()
$table = new HTML_table();
$table->setCaption($caption);
$table->updateAttributes(array('summary' => 'modules'));

// prepare the DataGrid
$dg =& new Structures_DataGrid();
if (PEAR::isError($dg)) {
   die($dg->getMessage() . '<br />' . $dg->getDebugInfo());
}

// bind some data (e.g. via a SQL query and MDB2)
$error = $dg->bind($d);

$dg->fill($table);

if (PEAR::isError($error)) {
   die($dg->getMessage() . '<br />' . $dg->getDebugInfo());
}

// the renderer adds an auto-generated column for the checkbox by default;
// it is also possible to add a column yourself, for example like in the
// following four lines:
$column = new Structures_DataGrid_Column('checkboxes', 'idList', null,
                                         array('width' => '10'));
$dg->addColumn($column);
$dg->generateColumns();

$rendererOptions = array('form'         => $form,
                         'formRenderer' => $renderer,
                         'inputName'    => 'idList',
                         'primaryKey'   => 'id'
                        );

$error = $dg->getOutput('CheckableHTMLTable', $rendererOptions);
if (PEAR::isError($error)) {
   die($dg->getMessage() . '<br />' . $dg->getDebugInfo());
}

// use a template string for the form
$tpl = $error;

// add the HTML code of the action selectbox and the submit button to the template string
$tpl .= $renderer->elementToHtml('action');
$tpl .= $renderer->elementToHtml('submit');

// we're now ready to output the form (toHtml() adds the <form> / </form> pair to the template)
echo $renderer->toHtml($tpl);

$dg->fill($table);

//echo $table->toHTML();

// if the form was submitted and the data is valid, show the submitted data
if ($form->isSubmitted() && $form->validate()) {
    var_dump($form->getSubmitValues());
}


?>

<?php echo $debtor->dbquery->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>
