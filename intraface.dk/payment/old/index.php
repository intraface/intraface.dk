<?php
/**
 * Online payment
 *
 * Contains:
 *
 */

require_once '../include_first.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// cleaning out values before making the transaction
	require_once 'Validate/Finance/CreditCard.php';

	$required = array('cardnumber', 'cvd', 'expirationdate_year', 'expirationdate_month');

	foreach ($required AS $r) {
		if (!isset($_POST[$r])) {
			$error[] = 'required field not set';
			break;
		}
	}

	if (!Validate_Finance_CreditCard::number($_POST['cardnumber'])) {
		$error[] = 'creditnumber not correct';
	}

	if (strlen($_POST['cvd']) != 3) {
		$error[] = 'cvd number has to be three digits';
	}

	if (strlen($_POST['expirationdate_year']) != 2 || !is_numeric($_POST['expirationdate_year'])) {
		$error[] = 'error in expiration year';
	}
	if (strlen($_POST['expirationdate_month']) != 2 || !is_numeric($_POST['expirationdate_month'])) {
		$error[] = 'error in expiration month';
	}


	if (!empty($error) AND count($error) > 0) {
		echo 'error';
	}
	else {
		// make transaction with quickpay
		require_once 'Payment/Quickpay.php';

		$eval = false;
		try {
			$qp = new quickpay;
			$qp->set_curl_certificate('Intraface/certificates/cacert.pem');
			$qp->set_msgtype('1100');
			$qp->set_md5checkword('73XuwSrE9qM12vb9kAIx26mD1n8K5YQhFyt8U6eGZl66J1NH8fcT3pjCB55L42iP');
			$qp->set_merchant('29991634');
			$qp->set_posc('K00500K00130');
			$qp->set_cardnumber($_POST['cardnumber']);
			$qp->set_expirationdate($_POST['expirationdate_year'] . $_POST['expirationdate_month']);
			$qp->set_cvd($_POST['cvd']);
			$qp->set_ordernum('1000'); // MUST at least be of length 4
			$qp->set_amount('100');
			$qp->set_currency('DKK');

			$qp->add_customVars('Name1', 'Value1');
			$qp->add_customVars('Name2', 'Value2');

			$eval = $qp->authorize();
		}
		catch (Exception $e) {
			echo "<b>Caught an exception in \"". $e->getFile() . "\" at line " . $e->getLine() . "</b><br />" . $e->getMessage() . "<br />";
		}

		if ($eval) {
			if ($eval['qpstat'] === '000') { // The authorization was completed
				echo 'Authorization: ' . $eval['qpstat'] . '<br />';
				echo "<pre>";
				var_dump($eval);
				echo "</pre>";
			}
			else { // an error occured
				echo 'Authorization: ' . $eval['qpstat'] . '<br />';
				echo "<pre>";
				var_dump($eval);
				echo "</pre>";
			}
		}
		else {
			echo 'Communication Error - CURL might not be properly installed';
		}

		// sende en e-mail med resultatet uanset hvordan det går.
	}

}

?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">
	<fieldset>
		<div class="formrow">
			<label for="cardnumber">Kortnummer</label>
			<input name="cardnumber" id="cardnumber" value="" />
		</div>
		<div class="formrow">
			<label for="expirationdate_month">Måned</label>
			<input name="expirationdate_month" id="expirationdate_month" value="" />
		</div>

		<div class="formrow">
			<label for="expirationdate_year">År</label>
			<input name="expirationdate_year" id="expirationdate_year" value="" />
		</div>
		<div class="formrow">
			<label for="cvd">Sikkerhedsnummer</label>
			<input name="cvd" id="cvd" value="" />
		</div>
		<div class="submit">
			<input type="submit" value="Send" />
		</div>
	</fieldset>
</form>
