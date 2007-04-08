var page_edit = {

	init: function() {
		page_edit.fieldset = document.getElementById("cms-page-info");
		page_edit.select = document.getElementById("cms-page-type");
		YAHOO.util.Event.addListener(page_edit.select, "change", page_edit.toggle);
		page_edit.toggle();
	},
	
	toggle: function() {
		if (page_edit.select.value == "page") {
			page_edit.fieldset.style.display = "block";
		}
		else {
			page_edit.fieldset.style.display = "none";		
		}
	}
	

}

YAHOO.util.Event.addListener(window, "load", page_edit.init);