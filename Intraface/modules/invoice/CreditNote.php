<?php

class CreditNote extends Debtor {

	function CreditNote(& $kernel, $id = 0) {
		Debtor::Debtor($kernel, 'credit_note', $id);
	}

	function setStatus($status) {

		$return = Debtor::setStatus($status);
		if($status == "sent") {
			// Er den sendt, bliver den også låst
			return Debtor::setStatus("executed");
		}
		else {
			return $return;
		}
	}

	function delete() {
		if($this->get("where_from") == "invoice" && $this->get("where_from_id") != 0) {
			$invoice = Debtor::factory($this->kernel, $this->get("where_from_id"));
		}
		Debtor::delete();
		if(isset($invoice)) {
			$invoice->updateStatus();
		}
	}

	function creditnoteReadyForState() {
		if (!$this->readyForState()) {
			return 0;
		}
		if ($this->type != 'credit_note') {
			$this->error->set('Du kan kun bogføre kreditnotaer');
			return 0;
		}

		$this->loadItem();
		$items = $this->item->getList();
		for ($i = 0, $max = count($items); $i < $max; $i++) {
			$product = new Product($this->kernel, $items[$i]['product_id']);
			if ($product->get('state_account_id') == 0) {
				$this->error->set('Produktet ' . $product->get('name') . ' ved ikke hvor den skal bogføres');
			}
		}
		if ($this->error->isError()) {
			return 0;
		}
		return 1;
	}

function state($year, $voucher_number, $voucher_date) {
		$validator = new Validator($this->error);
		if($validator->isDate($voucher_date, "Ugyldig dato")) {
			$this_date = new Intraface_Date($voucher_date);
			$this_date->convert2db();
		}
		// FIXME check date
		if ($this->isStated()) {
			$this->error->set('Allerede bogført');
			return 0;
		}
		if (!$this->creditnoteReadyForState()) {
			$this->error->set('Ikke klar til bogføring');
			return 0;
		}
		if ($this->get('type') != 'credit_note') {
			$this->error->set('Ikke en Kreditnota');
			return 0;
		}

		if (!$this->kernel->user->hasModuleAccess('accounting')) {
			trigger_error('Ikke rettigheder til at bogføre', E_USER_ERROR);
		}

		$this->kernel->useModule('accounting');



		// hente alle produkterne på debtor
		$this->loadItem();
		$items = $this->item->getList();

		$voucher = Voucher::factory($year, $voucher_number);
		$voucher->save(array(
			'voucher_number' => $voucher_number,
			'date' => $voucher_date,
			'text' => 'Kreditnota #' . $this->get('number')
		));


		$total = 0;

		foreach($items AS $item) {

			// produkterne
			// bemærk at denne går ud fra at alt skal overføres til debtorkontoen som standard
			$product = new Product($this->kernel, $item['product_id']);
			$debet_account = Account::factory($year, $product->get('state_account_id'));
			$debet_account_number = $debet_account->get('number');
			$credit_account = new Account($year, $year->getSetting('debtor_account_id'));
			$credit_account_number = $credit_account->get('number');
			$voucher = Voucher::factory($year, $voucher_number);

			$amount = $item['quantity'] * $item['price'];

			// hvis beløbet er mindre end nul, skal konti byttes om og beløbet skal gøres positivt
			if ($amount < 0) {
				$debet_account_number = $credit_account->get('number');
				$credit_account_number = $debet_account->get('number');
				$amount = abs($amount);
			}

			$input_values = array(
				'voucher_number' => $voucher_number,
				'invoice_number' => $this->get('number'),
				'date' => $voucher_date,
				'amount' => number_format($amount, 2, ",", "."),
				'debet_account_number' => $debet_account_number,
				'credit_account_number' => $credit_account_number,
				'vat_off' => 1,
				'text' => 'Kreditnota #' . $this->get('number') . ' - ' . $item['name']
			);
			if ($credit_account->get('vat_off') == 0) {
				$total += $item["quantity"] * $item["price"];
			}

			if (!$voucher->saveInDaybook($input_values, true)) {
				$voucher->error->view();
			}
		}
		// samlet moms på fakturaen
		// opmærksom på at momsbeløbet her er hardcoded - og det bør egentlig tages fra fakturaen?
		$voucher = Voucher::factory($year, $voucher_number);
		$debet_account = new Account($year, $year->getSetting('vat_out_account_id'));
		$credit_account = new Account($year, $year->getSetting('debtor_account_id'));
		$input_values = array(
				'voucher_number' => $voucher_number,
				'invoice_number' => $this->get('number'),
				'date' => $voucher_date,
				'amount' => number_format($total * $this->kernel->setting->get('intranet', 'vatpercent') / 100, 2, ",", "."), // opmærksom på at vat bliver rigtig defineret
				'debet_account_number' => $debet_account->get('number'),
				'credit_account_number' => $credit_account->get('number'),
				'vat_off' => 1,
				'text' => 'Kreditnota #' . $this->get('number') . ' - ' . $debet_account->get('name')
		);


		if (!$voucher->saveInDaybook($input_values, true)) {
			$voucher->error->view();
		}

		$this->setStated($voucher->get('id'), $this_date->get());

		$voucher_file = new VoucherFile($voucher);
		if (!$voucher_file->save(array('description' => 'Kreditnota ' . $this->get('number'), 'belong_to'=>'credit_note','belong_to_id'=>$this->get('id')))) {
			$voucher_file->error->view();
			$this->error->set('Filen blev ikke overflyttet');
		}

		$this->load();

		return 1;

	}


}

?>