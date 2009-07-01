var page_edit = {

	init: function() {
		page_edit.fieldset = document.getElementById("cms-page-info");
		page_edit.select = document.getElementById("cms-page-type");
		page_edit.view_type = document.getElementById("static-cms-page-type");
		YAHOO.util.Event.addListener(page_edit.select, "change", page_edit.toggle);
		page_edit.toggle();
		
		page_edit.select.style.display = "none";
		page_edit.view_type.style.display = "";
	},
	
	toggle: function() {
		if (page_edit.select.value == "page") {
			page_edit.fieldset.style.display = "block";
		}
		else if(page_edit.fieldset) {
			page_edit.fieldset.style.display = "none";		
		}
	},
	
	show_select: function() {
		page_edit.select.style.display = "";
		page_edit.view_type.style.display = "none";
	},
	
	fill_shortlink: function() {
	   if(document.getElementById('shortlink').value == '') {
	       document.getElementById('shortlink').value = parseUrlIdentifier(document.getElementById('title').value);
	   }
	}
}

YAHOO.util.Event.addListener(window, "load", page_edit.init);
