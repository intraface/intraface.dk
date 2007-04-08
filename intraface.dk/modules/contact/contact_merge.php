<?php
/**
 * Denne fil skal kunne flette en kontakt med en anden kontakt.
 *
 * Det betyder at filen skal vide, hvor kontakterne findes.
 *
 * Kontakterne findes i:
 * - debtor
 * - newsletter
 * - procurement
 *
 * Kontakten bør slettes efterfølgende, når man har flettet kontakten.
 * Det er muligt at der er de pågældende klasser skal være en metode
 * til at flette kontakter, og der skal i hvert fald være en masse spørgsmål,
 * om man nu er sikker gennem processen.
 */
require('../../include_first.php');

$contact_module = $kernel->module('contact');
$invoice_module = $kernel->useModule('debtor');

$translation = $kernel->getTranslation('contact');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// this contact
	$contact = new Contact($kernel, intval($_POST['id']));

	// any to merge with?
	if (is_array($_POST['contact'])) {

		foreach($_POST['contact'] AS $key=>$value) {

			// which contact to merge with
			$contact_id = intval($_POST['contact'][$key]);
			$merge_with_contact = new Contact($kernel, $contact_id);

			// debtors
			$debtor = new Debtor($kernel, 'invoice');
			$debtor->dbquery->setCondition('contact_id='.$merge_with_contact->get('id'));
			foreach ($debtor->getList() AS $debtor_array) {
				$debtor = new Debtor($kernel, $debtor_array['id']);
				echo $debtor->get('id');
				//$debtor->setNewContact($contact->get('id'));
			}


		}
		/*
		$old_contact_id = $contact->get("id");
		$debtor->setNewContact($_GET['new_id']);
		$contact = new Contact($kernel, $old_contact_id);

		if ($debtor->get('where_from') == 'webshop') {
			$contact->delete();
		}
		$debtor->load();
		*/

	}

}

if (isset($_GET['action']) AND $_GET['action'] == "changecontact") {
/*
	if (isset($_GET['new_id']) AND is_numeric($_GET['new_id'])) {
	}
*/
}


$contact = new Contact($kernel, $_GET['id']);
$similar_contacts = $contact->compare();


$page = new Page($kernel);
$page->start('Kontakter');
?>
<h1>Flet kontakter</h1>

<?php if(!is_array($similar_contacts) OR count($similar_contacts) == 0) {	?>

<p>Denne kontakt ligner ikke andre, så du kan ikke flette den med nogen.</p>

<?php } else { ?>


	<table style="font-size: 0.8em;">
		<caption>Denne kontakt</caption>
		<thead>
	  	<tr>
			<th></th>
			<th>Nummer</th>
			<th>Navn</th>
			<th>Adresse</th>
			<th>Postby</th>
			<th>Telefon</th>
			<th>E-mail</th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td></td>
				<td><?php echo htmlspecialchars($contact->get('number')); ?></td>
				<td><?php echo htmlspecialchars($contact->address->get('name')); ?></td>
				<td><?php echo htmlspecialchars($contact->address->get('address')); ?></td>
				<td><?php echo htmlspecialchars($contact->address->get('postcode')); ?> <?php echo $contact->get('city'); ?></td>
				<td><?php echo htmlspecialchars($contact->address->get('phone')); ?></td>
				<td><?php echo htmlspecialchars($contact->address->get('email')); ?></td>
			</tr>
		</tbody>
	</table>


	<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
	<input type="hidden" value="<?php echo intval($contact->get('id')); ?>" name="id" />
	<table style="font-size: 0.8em;">
		<caption>Ligner følgende kontakter</caption>
		<thead>
	  	<tr>
			<th></th>
			<th>Nummer</th>
			<th>Navn</th>
			<th>Adresse</th>
			<th>Postby</th>
			<th>Telefon</th>
			<th>E-mail</th>
			<th>Fakturaer</th>
		</tr>
		</thead>
		<tbody>
		<?php
			foreach($similar_contacts AS $c) {
			?>
			<tr>
				<td><input type="checkbox" value="<?php echo intval($c['id']); ?>" name="contact[]" /></td>
				<td><?php echo htmlspecialchars($c['number']); ?></td>
				<td><?php echo htmlspecialchars($c['name']); ?></td>
				<td><?php echo htmlspecialchars($c['address']); ?></td>
				<td><?php echo htmlspecialchars($c['postcode']); ?> <?php echo $c['city']; ?></td>
				<td><?php echo htmlspecialchars($c['phone']); ?></td>
				<td><?php echo htmlspecialchars($c['email']); ?></td>
				<td><?php echo htmlspecialchars($c['email']); ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<p><input type="submit" value="Flet kontakter" /></p>
	</form>
<?php } ?>

<?php
$page->end();
?>

