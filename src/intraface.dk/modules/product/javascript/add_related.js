var related = {
	init:function() {
		if (!document.getElementById) return;
		publish.product_id = document.getElementById('product_id').value;
		publish.elements = YAHOO.util.Dom.getElementsByClassName('input-relate');
		YAHOO.util.Event.addListener(publish.elements, "change", publish.startRequest); 
	},
 
	handleSuccess:function(o){ 
		// This member handles the success response 
		// and passes the response object o to AjaxObject's 
		// processResult member. 
		publish.processResult(o); 
	}, 

	handleFailure:function(o){ 
	}, 

	processResult:function(o){ 
		// This member is called by handleSuccess
	}, 
	
	startRequest:function() { 
		var value = '';
		if (this.checked) { 
			value = 'relate';
		} 
		YAHOO.util.Connect.asyncRequest('POST', 'related_product.php', callback, "id=" + publish.product_id + "&product[" + this.id + "]=" + this.id + "&relate[" + this.id + "]=" + value + "&ajax=true");		 

	} 
 
}; 

var callback = { 
	success:related.handleSuccess, 
	failure:related.handleFailure, 
	scope: related 
}; 

//YAHOO.util.Event.addListener(window, "load", checkboxes.init);
YAHOO.util.Event.addListener(window, "load", related.init); 
