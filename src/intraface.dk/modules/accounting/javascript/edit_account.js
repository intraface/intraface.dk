var account = {
	init: function() {
		var compatible = document.getElementById;
		if (!compatible) return;
		if (document.getElementById("account")) {
			var accounttype = document.getElementById("account_type");
			account.toggle(accounttype);
			YAHOO.util.Event.addListener(accounttype, "change", (function(accounttype) { 
				return function() { 
					account.toggle(accounttype); 
				}; 
			})(accounttype));
			
		}

	},
	
	toggle: function(accounttype) {
		var vat = document.getElementById("vat_fieldset");
		var sum = document.getElementById("sum_fieldset");
		var use = document.getElementById("use_fieldset");
		if (accounttype.value == '2' || accounttype.value == '3' || accounttype.value == '4') { // drift og status
			if (vat) vat.style.display = "block";
			if (sum) sum.style.display = "none";
			if (use) use.style.display = "block";								
		}
		else if (accounttype.value == '5') { // sum
			if (sum) sum.style.display = "block";
			if (vat) vat.style.display = "none";	
			if (use) use.style.display = "none";			
		}
		else if (accounttype.value == '1' || accounttype.value == '') { // headline
			if (vat) vat.style.display = "none";
			if (sum) sum.style.display = "none";
			if (use) use.style.display = "none";								
		}
	
	}
	
}

YAHOO.util.Event.addListener(window, "load", account.init);
