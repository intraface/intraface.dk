<?php
require('../../include_first.php');
$module = $kernel->module("debtor");

$mainInvoice = $kernel->useModule("invoice");

$reminder = new Reminder($kernel);

settype($_GET["contact_id"], 'integer');
$contact_id = $_GET["contact_id"];


if (isset($_GET["delete"])) {
	$reminder = new Reminder($kernel, (int)$_GET["delete"]);
	$reminder->delete();
}

if ($contact_id) {
	$contact = new Contact($kernel, $contact_id);
	$reminder->getDBQuery()->setFilter("contact_id", $contact->get("id"));
}

if (isset($_GET["search"])) {
	if (isset($_GET["text"]) && $_GET["text"] != "") {
		$reminder->getDBQuery()->setFilter("text", $_GET["text"]);
	}

	if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
		$reminder->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
	}

	if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
		$reminder->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
	}

	if (isset($_GET["status"])) {
		$reminder->getDBQuery()->setFilter("status", $_GET["status"]);
	}
} else {
	if ($reminder->getDBQuery()->checkFilter("contact_id")) {
        $reminder->getDBQuery()->setFilter("status", "-1");
    } else {
		$reminder->getDBQuery()->setFilter("status", "-2");
	}
}

$reminder->getDBQuery()->usePaging("paging");
$reminder->getDBQuery()->storeResult("use_stored", "reminder", "toplevel");
$reminders = $reminder->getList();

$page = new Intraface_Page($kernel);
$page->start("Rykkere");
?>

<h1>Rykkere</h1>

<?php if ($contact_id): ?>
	<ul class="options">
		<li><a href="reminder_edit.php?contact_id=<?php e($contact_id); ?>">Opret rykker</a></li>
		<li><a href="/modules/contact/contact.php?id=<?php e($contact->get('id')); ?>">Gå til kontakten</a>
	</ul>
<?php endif; ?>


<?php if (!$reminder->isFilledIn()): ?>

<p>Der er ikke oprettet nogen rykkere endnu. Du har nok nogle gode kunder. Rykkere oprettes fra en faktura.</p>

<?php else: ?>

<?php
echo $reminder->error->view();
?>

<?php
if ($contact_id == 0) {
	?>
	<form method="get" action="reminders.php">
	<fieldset>
		<legend>Find</legend>
		Tekst: <input type="text" name="text" value="<?php e($reminder->getDBQuery()->getFilter("text")); ?>" />
		Status:
		<select name="status">
			<option value="-1">Alle</option>
			<option value="-2"<?php if ($reminder->getDBQuery()->getFilter("status") == -2) echo ' selected="selected"';?>>Åbne</option>
			<option value="0"<?php if ($reminder->getDBQuery()->getFilter("status") == 0) echo ' selected="selected"';?>>Oprettet</option>
			<option value="1"<?php if ($reminder->getDBQuery()->getFilter("status") == 1) echo ' selected="selected"';?>>Sendt</option>
			<option value="2"<?php if ($reminder->getDBQuery()->getFilter("status") == 2) echo ' selected="selected"';?>>Afsluttet</option>
			<option value="3"<?php if ($reminder->getDBQuery()->getFilter("status") == 3) echo ' selected="selected"';?>>Annulleret</option>
		</select>
		Fra dato: <input type="text" name="from_date" value="<?php e($reminder->getDBQuery()->getFilter("from_date")); ?>" />
		Til dato: <input type="text" name="to_date" value="<?php e($reminder->getDBQuery()->getFilter("to_date")); ?>" />
		<input type="submit" name="search" value="Find" />
	</fieldset>
	</form>
	<?php
}
?>

<table class="stripe">
<thead>
	<tr>
		<th>Nr.</th>
		<th>Kunde</th>
		<th>Beskrivelse</th>
		<th>Sendt</th>
		<th>Sendt som</th>
		<th>Forfaldsdato</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody>
	<?php
	for ($i = 0, $n = count($reminders); $i < $n; $i++) {
		?>
		<tr id="i<?php e($reminders[$i]["id"]); ?>"<?php if (isset($_GET['id']) && $_GET['id'] == $reminders[$i]['id']) print(" class=\"fade\""); ?>>
			<td class="number"><?php e($reminders[$i]["number"]); ?></td>
			<td><a href="reminders.php?contact_id=<?php e($reminders[$i]["contact_id"]); ?>"><?php e($reminders[$i]["name"]); ?></a></td>
			<td><a href="reminder.php?id=<?php e($reminders[$i]["id"]); ?>"><?php (trim($reminders[$i]["description"] != "")) ? e($reminders[$i]["description"]) : e(t("[No description]")); ?></a></td>
			<td class="date">
				<?php
				if ($reminders[$i]["status"] != "created") {
					e($reminders[$i]["dk_date_sent"]);
				}
				else {
					e(t('No'));
				}
				?>
      </td>
			<td><?php e($reminders[$i]["send_as"]); ?></td>
			<td class="date">
				<?php
				if ($reminders[$i]["status"] == "executed" || $reminders[$i]["status"] == "canceled") {
					e($reminders[$i]["status"]);
				} elseif ($reminders[$i]["due_date"] < date("Y-m-d")) { ?>
					<span class="red"><?php e($reminders[$i]["dk_due_date"]); ?></span>
				<?php
                } else {
					e($reminders[$i]["dk_due_date"]);
				}
				?>
			</td>
			<td class="buttons">
				<?php
				if ($reminders[$i]["locked"] == 0) {
					?>
					<a class="edit" href="reminder_edit.php?id=<?php e($reminders[$i]["id"]); ?>">Ret</a>
					<?php if ($reminders[$i]["status"] == "created"): ?>
					<a class="delete" href="reminders.php?contact_id=<?php e($_GET["contact_id"]); ?>&amp;delete=<?php e($reminders[$i]["id"]); ?>">Slet</a>
					<?php endif; ?>
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}
	?>
</tbody>
</table>
<?php echo $reminder->getDBQuery()->display('paging'); ?>

<?php endif; ?>

<?php
$page->end();
?>