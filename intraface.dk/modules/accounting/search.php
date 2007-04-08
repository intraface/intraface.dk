<?php
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

// set year
$year = new Year($kernel);
$year->checkYear();

if (!empty($_POST)) {

	$search_terms = ('bilag');

	if (!empty($_POST['search'])) {
		$search_string = $_POST['search'];
		$search = explode(':', $_POST['search']);
		$search_term = $search[0];
		$search_real = $search[1];
		if (strpos($search[1], '-')) {
			$search_real = explode('-', $search_real);
		}
		if (count($search_real) <> 2) {
			trigger_error('Ulovlig søgning - ikke bindestreg');
		}


		switch ($search_term) {
			case 'bilag':
				$db = new DB_Sql;
				$db->query("SELECT * FROM accounting_voucher WHERE number >= " . $search_real[0] . " AND number <= " . $search_real[1] . " AND intranet_id = " . $year->kernel->intranet->get('id') . " AND year_id = " . $year->get('id'));
				//$i++;
				$posts = array();
				while ($db->nextRecord()) {
					$voucher = new Voucher($year, $db->f('id'));
					$posts = array_merge($voucher->getPosts(), $posts);
					//$i++;
				}
				break;
			default:
					trigger_error('Ulovlig søgning', E_USER_ERROR);
				break;
		}

	}



}

// søg
/*
if (!empty($_POST['submit']) OR isset($_REQUEST['voucher_number'])) {

	$sql = "";

	if (!empty($_POST['date_from']) OR !empty($_POST['date_to'])) {
		if (!empty($_POST['date_from'])) { $date_from = $_POST['date_from']; }
		else { $date_from = date('Y-m-d'); }
		if (!empty($_POST['date_to'])) { $date_to = $_POST['date_to']; }
		else { $date_to = date('Y') . "-12-31"; }

		if (isset($date_from) OR isset($date_to)) {
			$sql = "SELECT post.date, post.voucher_number, account.account_number, post.text, post.debet, post.credit
				FROM accounting_post post
				LEFT JOIN accounting_account account
					ON account.id = post.account_id
				WHERE
					post.date >= '".$date_from."'
					AND post.date <= '".$date_to."'
					AND post.year_id = " . $year->get('id') . "
					AND post.intranet_id = " . $kernel->intranet->get('id') . "
				ORDER BY post.voucher_number ASC";
		}
	}

	if (!empty($_POST['voucher_number_from']) OR !empty($_POST['voucher_number_to'])) {

		$sql = "SELECT post.*
			FROM accounting_post post
			LEFT JOIN accounting_account account
				ON account.id = post.account_id
			WHERE
				post.voucher_number >= ".(int)$_POST['voucher_number_from']."
				AND post.voucher_number <= ".(int)$_POST['voucher_number_to']."
				AND post.year_id = " . $year->get('id') . "
				AND post.intranet_id = " . $kernel->intranet->get('id') . "
			ORDER BY post.voucher_number ASC";
	}
	// denne virker fint nok
	if (!empty($_POST['invoice_number'])) {
		$sql = "SELECT * FROM accounting_post post
			LEFT JOIN accounting_account account ON account.id = post.account_id
			WHERE invoice_number = '".$_POST['invoice_number']."'
			AND post.year_id = " . $year->get('id') . " AND post.intranet_id = " . $kernel->intranet->get('id');
	}

	// så bilagsnumrene bliver umiddelbart klikbare fra søgningen
	if (!empty($_REQUEST['voucher_number'])) {
		$sql = "SELECT post.*
			FROM accounting_post post
				LEFT JOIN accounting_account account
					ON account.id = post.account_id
			WHERE voucher_number = '".$_REQUEST['voucher_number']."'
				AND post.year_id = " . $year->get('id') . "
				AND post.intranet_id = " . $kernel->intranet->get('id');
	}

	// set values
	$date_from = $_POST['date_from'];
	$date_to = $_POST['date_to'];
	$voucher_number_to = $_POST['voucher_number_to'];
	$voucher_number_from = $_POST['voucher_number_from'];
	$invoice_number = $_POST['invoice_number'];

}

$posts = array();

if (!empty($sql)) {

	$db = new DB_Sql;
	$db->query($sql);

	// print("Antal poster: ".$db->numRows());
	$i = 0;
	while ($db->nextRecord()) {
		$posts[$i]["date"] = $db->f("date");
		$posts[$i]["voucher_number"] = $db->f("voucher_number");
    $account = new Account($year, $db->f('account_id'));
		$posts[$i]["account_number"] = $account->get('number');
    $posts[$i]["account_name"] = $account->get('name');
		$posts[$i]["debet"] = $db->f("debet");
		$posts[$i]["credit"] = $db->f("credit");
		$i++;
	}

}
*/

$page = new Page($kernel);
$page->start('Find posteringer');
?>

<h1>Posteringer</h1>

<p>Søgningen søger i alle bilag, som er bogført. Du skal nok finde det, hvis det er der.</p>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>" id="find_posts">
<!--
<fieldset>

<legend>Søg efter poster</legend>
	<fieldset>
		<legend>Dato (åååå-mm-dd)</legend>
		<div>
			<label for="date_from">Fra</label>
			<input type="text" name="date_from" id="date_from" value="<?php if (!empty($date_from)) echo safeToHtml($date_from); ?>" />
			<label for="date_to">Til</label>
			<input type="text" name="date_to" id="date_to" value="<?php if (!empty($date_to)) echo safeToHtml($date_to); ?>" />
		</div>
	</fieldset>
	<fieldset>
		<legend>Bilag (indtast nummer)</legend>
		<div>
			<label for="voucher_number_from">Fra</label>
			<input type="text" name="voucher_number_from" id="voucher_number_from" value="<?php if(!empty($voucher_number_from)) echo safeToHtml($voucher_number_from); ?>" />
			<label for="voucher_number_to">Til</label>
			<input type="text" name="voucher_number_to" id="voucher_number_to" value="<?php if (!empty($voucher_number_to)) echo $voucher_number_to; ?>" />
		</div>
	</fieldset>
	<fieldset>
		<legend>Faktura</legend>
		<div>
			<label for="invoice_number">Nummer</label>
			<input type="text" name="invoice_number" id="invoice_number" value="<?php if (!empty($invoice_number)) echo safeToHtml($invoice_number); ?>" />
		</div>
	</fieldset>
</fieldset>
	-->

	<fieldset>
		<legend>Søg</legend>
		<p>Foreløbig kan du lave følgende søgning: <samp>Bilag: 1-2</samp>, og vi skynder os at finde bilag 1 til 2 til dig.</p>
		<div>
			<label for="search">Søg</label>
			<input type="text" name="search" id="search" value="<?php if(!empty($search_string)) echo safeToForm($search_string); ?>" />
		</div>

		<div>
			<input type="submit" name="submit" value="Afsted!" />
		</div>

	</fieldset>



</form>

<?php if(!empty($posts) AND is_array($posts) AND count($posts) > 0): ?>

<table>
	<caption>Bilag</caption>
	<thead>
	<tr>
		<th>Dato</th>
		<th>Bilag</th>
		<th>Kontonummer</th>
		<th>Debet</th>
		<th>Credit</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($posts AS $post) { ?>
	<tr>
		<td><?php echo $post['date_dk']; ?></td>
		<td><a href="voucher.php?id=<?php echo $post['voucher_id']; ?>"><?php echo safeToHtml($post['voucher_number']); ?></a></td>
		<td><?php echo safeToHtml($post['account_number']) . ' ' . safeToHtml($post['account_name']); ?></td>
		<td><?php echo amountToOutput($post['debet']); ?></td>
		<td><?php echo amountToOutput($post['credit']); ?></td>
	</tr>
	<?php } ?>
	</tbody>
</table>

<?php elseif (!empty($search_string)): ?>
	<p>Der blev ikke fundet nogen posteringer ud fra din søgning.</p>
<?php endif; ?>

<?php
$page->end();
?>