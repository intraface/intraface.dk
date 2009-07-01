var debtor = {
	init: function() {
		var compatible = document.getElementById;
		if (!compatible) return;

		if (document.getElementById("contact_person_id")) {
			var contact = document.getElementById("contact_person_id");
			debtor.toggle(contact);
			YAHOO.util.Event.addListener(contact, "change", (function(contacttype) { 
				return function() { 
					debtor.toggle(contact); 
				}; 
			})(contact));
			
		}
	},
	
	toggle: function(contactperson) {
		var o = document.getElementById("contactperson");
		if (contactperson.value == '-1') { // opret ny
			if (o) o.style.display = "block";
		}
		else {
			if (o) o.style.display = "none";		
		}
	}

	
}

YAHOO.util.Event.addListener(window, "load", debtor.init);
