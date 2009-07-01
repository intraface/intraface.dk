
function fillDateFields() {
	
	if(document.getElementById('dk_delivery_date').value == '') {
		document.getElementById('dk_delivery_date').value = document.getElementById('dk_invoice_date').value;
	}
	
	if(document.getElementById('dk_payment_date').value == '') {
		document.getElementById('dk_payment_date').value = document.getElementById('dk_invoice_date').value;
	}
}