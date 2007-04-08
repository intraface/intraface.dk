<?php

class Quotation extends Debtor {

	function Quotation(& $kernel, $id = 0) {
		Debtor::Debtor($kernel, 'quotation', $id);
	}

}

?>