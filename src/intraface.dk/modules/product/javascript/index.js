Event.observe(window, 'load', initProduct, false);

function initProduct() {

  var node = $("maincontent");
  
	var oDelete = getElementsByClass("ajaxdelete", node);
	var n = oDelete.length;
	for (var i=0;i<n;i++){
    oDelete[i].onclick = deleteObject;
	}

}

function deleteObject() {

	var url = "/modules/product/index.php";
	var pars = "delete=" + this.id.substring(6);

	var myAjax = new Ajax.Updater(
			{success: Message}, 
			url, 
			{method: 'get', parameters: pars, onFailure: reportError}
		);
}

function Message() {
	Insertion.before(document.getElementById("product_table"), "slettet");
}

function reportError() {
	return false;
}

