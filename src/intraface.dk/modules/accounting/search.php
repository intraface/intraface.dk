<?php
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

// set year
$year = new Year($kernel);
$year->checkYear();

$error = new Intraface_Error;

// @todo this has to be made much better
if (!empty($_POST)) {

	$search_terms = array('bilag');

	if (!empty($_POST['search'])) {
		$search_string = $_POST['search'];
		$search = explode(':', $_POST['search']);

        if (empty($search[0]) OR empty($search[1])) {
            $error->set('Not a valid search');
        } else {

    		$search_term = $search[0];
    		$search_real = $search[1];
    		if (strpos($search[1], '-')) {
    			$search_real = explode('-', $search_real);
    		} else {
                $error->set('Not a valid search');
    		}

            if (!$error->isError()) {
        		$search_term = strtolower($search_term);

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
        					$error->set('Not a valid search');
        				break;
        		}
            }
        }
	} else {
        $error->set('Not a valid search');
	}



}

$page = new Intraface_Page($kernel);
$page->start('Find posteringer');
?>

<h1>Find posteringer</h1>

<?php echo $error->view(); ?>

<p>Søgningen søger i alle bilag, som er bogført. Du skal nok finde det, hvis det er der.</p>

<form method="post" action="<?php e($_SERVER['PHP_SELF']); ?>" id="find_posts">
<!--
<fieldset>

<legend>Søg efter poster</legend>
	<fieldset>
		<legend>Dato (åååå-mm-dd)</legend>
		<div>
			<label for="date_from">Fra</label>
			<input type="text" name="date_from" id="date_from" value="<?php if (!empty($date_from)) e($date_from); ?>" />
			<label for="date_to">Til</label>
			<input type="text" name="date_to" id="date_to" value="<?php if (!empty($date_to)) e($date_to); ?>" />
		</div>
	</fieldset>
	<fieldset>
		<legend>Bilag (indtast nummer)</legend>
		<div>
			<label for="voucher_number_from">Fra</label>
			<input type="text" name="voucher_number_from" id="voucher_number_from" value="<?php if (!empty($voucher_number_from)) e($voucher_number_from); ?>" />
			<label for="voucher_number_to">Til</label>
			<input type="text" name="voucher_number_to" id="voucher_number_to" value="<?php if (!empty($voucher_number_to)) e($voucher_number_to); ?>" />
		</div>
	</fieldset>
	<fieldset>
		<legend>Faktura</legend>
		<div>
			<label for="reference">Nummer</label>
			<input type="text" name="refernce" id="reference" value="<?php if (!empty($reference)) e($reference); ?>" />
		</div>
	</fieldset>
</fieldset>
	-->

	<fieldset>
		<legend>Søg</legend>
		<p>Foreløbig kan du lave følgende søgning: <samp>Bilag: 1-2</samp>, og vi skynder os at finde bilag 1 til 2 til dig.</p>
		<div>
			<label for="search">Søg</label>
			<input type="text" name="search" id="search" value="<?php if (!empty($search_string)) e($search_string); ?>" />
		</div>

		<div>
			<input type="submit" name="submit" value="Afsted!" />
		</div>

	</fieldset>



</form>

<?php if (!empty($posts) AND is_array($posts) AND count($posts) > 0): ?>

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
	<?php foreach ($posts as $post) { ?>
	<tr>
		<td><?php e($post['date_dk']); ?></td>
		<td><a href="voucher.php?id=<?php e($post['voucher_id']); ?>"><?php e($post['voucher_number']); ?></a></td>
		<td><?php e($post['account_number']) . ' ' . e($post['account_name']); ?></td>
		<td><?php e(amountToOutput($post['debet'])); ?></td>
		<td><?php e(amountToOutput($post['credit'])); ?></td>
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