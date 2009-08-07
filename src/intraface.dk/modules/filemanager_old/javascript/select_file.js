var select_file = {
	init:function() {
		if (!document.getElementById) return;
		select_file.redirect_id = document.getElementById('redirect_id').value;
		select_file.elements = YAHOO.util.Dom.getElementsByClassName('input-select_file');
		YAHOO.util.Event.addListener(select_file.elements, "change", select_file.startRequest); 
		select_file.submit_buttons = document.getElementById('submit_buttons');
		select_file.submit_buttons.innerHTML='<input type="submit" name="return" value="Overfør valg" />';
		/*
		select_file.options_top = document.getElementById('options_top');
		select_file.options_top.style.display="block";
		*/
	},
 
	handleSuccess:function(o){ 
		// This member handles the success response 
		// and passes the response object o to AjaxObject's 
		// processResult member. 
		
		select_file.processResult(o); 
		
	}, 

	handleFailure:function(o){ 
		// Failure handler
		select_file.submit_buttons.style.display = "block";
		alert('Automatisk gemning lykkedes ikke. Tryk Gem i bunden');

	}, 

	processResult:function(o){ 
		// This member is called by handleSuccess

	}, 
	
	startRequest:function() { 
		// YAHOO.util.Connect.asyncRequest('POST', 'site.php', callback, "id=" + publish.site_id+"&page="+this.id+"&status="+this.value+"&ajax=true");
		//alert("id=" + publish.site_id+"&page["+$this.id+"]="+this.id+"&status="+this.value+"&ajax=true");
		var action = 'remove_file_id';
		if (this.checked) { 
			action = 'add_file_id';
		} 
		// YAHOO.util.Connect.asyncRequest('POST', 'site.php', callback, "id=" + publish.site_id + "&page[" + this.id + "]=" + this.id + "&status[" + this.id + "]=" + value + "&ajax=true");		 
		YAHOO.util.Connect.asyncRequest('POST', 'select_file.php', callback, 'redirect_id=' + select_file.redirect_id + '&' + action + '=' + this.id + '&ajax=true');
	} 
 
}; 

var callback = { 
	success:select_file.handleSuccess, 
	failure:select_file.handleFailure, 
	scope: select_file 
}; 

//YAHOO.util.Event.addListener(window, "load", checkboxes.init);
YAHOO.util.Event.addListener(window, "load", select_file.init); 
















/*




function remove_file(id, redirect_id) {
	
	// fjerne link
	var link_div = document.getElementById('link_div_'+id);
	link_div.innerHTML = '';
	
	// eksekver data
	// Start the transaction.
	
	var result = ProcessSelect.startRequest('redirect_id='+redirect_id+'&remove_file_id='+id+'&ajax=true');
	
	
	// indsæt nyt link
	link_div.innerHTML = '<a href="select_file.php?use_stored=true&add_file_id='+id+'" onClick="add_file('+id+'); ?>, '+redirect_id+'); return false;">Tilføj</a>';

}


function add_file(id, redirect_id) {
	
	// fjerne link
	var link_div = document.getElementById('link_div_'+id);
	link_div.innerHTML = '';
	
	// eksekver data
	
	ProcessSelect.startRequest('redirect_id='+redirect_id+'&add_file_id='+id+'&ajax=true');
	
	// indsæt nyt link
	link_div.innerHTML = '<a href="select_file.php?use_stored=true&remove_file_id='+id+'" onClick="remove_file('+id+'); ?>, '+redirect_id+'); return false;">Fjern</a>';
}




/*
 * AjaxObject is a hypothetical object that encapsulates the transaction
 *     request and callback logic.
 *
 * handleSuccess( ) provides success case logic
 * handleFailure( ) provides failure case logic
 * processResult( ) displays the results of the response from both the
 * success and failure handlers
 * call( ) calling this member starts the transaction request.
 */

/*
var ProcessSelect = {

	handleSuccess:function(object){
		// This member handles the success response
		// and passes the response object o to AjaxObject's
		// processResult member.
		if(object.responseText != '1') {
			alert("Der opstod en fejl, da vi forsøgte at gemme data, forsøg evt. igen");
		}
	},

	handleFailure:function(o){
		// Failure handler
		alert("Vi kunne ikke gemme data, forsøg evt. igen");
	},

	processResult:function(o){
		// This member is called by handleSuccess
		
	},

	startRequest:function(querystring) {
		YAHOO.util.Connect.asyncRequest('POST', 'select_file.php', callback, querystring);
	}

};

/*
 * Define the callback object for success and failure
 * handlers as well as object scope.
 */
/*
var callback =
{
	success:ProcessSelect.handleSuccess,
	failure:ProcessSelect.handleFailure,
	scope: ProcessSelect,
};	
*/