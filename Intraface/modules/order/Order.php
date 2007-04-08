<?php

class Order extends Debtor {

	function Order(& $kernel, $id = 0) {
		Debtor::Debtor($kernel, 'order', $id);
	}

}

?>