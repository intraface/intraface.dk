var contact = {
	init: function() {
		var compatible = document.getElementById;
		if (!compatible) return;
		if (document.getElementById("contact-type")) {
			var contacttype = document.getElementById("contact-type");
			contact.toggleType(contacttype);
			YAHOO.util.Event.addListener(contacttype, "change", (function(contacttype) { 
				return function() { 
					contact.toggleType(contacttype); 
				}; 
			})(contacttype));
			
		}
		if (document.getElementById("preferred-invoice")) {
			var pref = document.getElementById("preferred-invoice");
			contact.toggleElectronic(pref);
			YAHOO.util.Event.addListener(pref, "change", (function(pref) { 
				return function() { 
					contact.toggleElectronic(pref); 
				}; 
			})(pref));
			
		}
		

	},
	
	toggleType: function(contacttype) {
		var corp = document.getElementById("corporate");
		if (contacttype.value == '1') { // firma
			if (corp) corp.style.display = "block";
		}
		else {
			if (corp) corp.style.display = "none";		
		}
	},

	toggleElectronic: function(pref) {
		var o = document.getElementById("invoice-electronic");
		if (pref.value == '3') { // firma
			if (o) o.style.display = "block";
		}
		else {
			if (o) o.style.display = "none";		
		}
	}

	
}

YAHOO.util.Event.addListener(window, "load", contact.init);
