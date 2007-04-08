var publish = {
	init:function() {
		if (!document.getElementById) return;
		publish.site_id = document.getElementById('site').value;
		publish.elements = YAHOO.util.Dom.getElementsByClassName('input-publish');
		YAHOO.util.Event.addListener(publish.elements, "change", publish.startRequest); 
		publish.submit = document.getElementById('submit-publish');
		publish.submit.style.display="none";
	},
 
	handleSuccess:function(o){ 
		// This member handles the success response 
		// and passes the response object o to AjaxObject's 
		// processResult member. 
		publish.processResult(o); 
	}, 

	handleFailure:function(o){ 
		// Failure handler
		publish.submit.style.display = "block"; 

	}, 

	processResult:function(o){ 
		// This member is called by handleSuccess

	}, 
	
	startRequest:function() { 
		// YAHOO.util.Connect.asyncRequest('POST', 'site.php', callback, "id=" + publish.site_id+"&page="+this.id+"&status="+this.value+"&ajax=true");
		//alert("id=" + publish.site_id+"&page["+$this.id+"]="+this.id+"&status="+this.value+"&ajax=true");
		var value = '';
		if (this.checked) { 
			value = 'published';
		} 
		YAHOO.util.Connect.asyncRequest('POST', 'site.php', callback, "id=" + publish.site_id + "&page[" + this.id + "]=" + this.id + "&status[" + this.id + "]=" + value + "&ajax=true");		 

	} 
 
}; 

var callback = { 
	success:publish.handleSuccess, 
	failure:publish.handleFailure, 
	scope: publish 
}; 

//YAHOO.util.Event.addListener(window, "load", checkboxes.init);
YAHOO.util.Event.addListener(window, "load", publish.init); 
