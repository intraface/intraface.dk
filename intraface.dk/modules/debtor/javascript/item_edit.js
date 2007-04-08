/*

Denne virker desværre ikke. Kan ikke submitte.
Måske er den bedste ide i virkeligheden også, at
det gemmes i cookies, så man har mulighed for at fortryde
igen?

Noget helt andet er, at sådan nogle skifter bør kunne
fungere også for dem, der ikke har slået javascript til,
så det er et spørgsmål, om skifterne i stedet skal laves
med nogle selectbokse?

*/

var compatible = document.getElementById;

function prepareLinks() {
	if (!compatible) return;
	/*
	var oForm = document.getElementById('items');
	if (!oForm) {
		return;
	}
	*/
	var oNav = getElementsByClass('characterNav');
	if (!oNav) {
		return;
	}

	var count = oNav.length;
	
	for (var i = 0; i < count; i++) {
		oLinks = oNav[i].getElementsByTagName("A");
	}
	
	count = oLinks.length;
	
	for (var i = 0; i < count; i++) {
		oLinks[i].onclick = function () {
			document.getElementById('form_items').submit();
		}
	}
	
}

YAHOO.util.Event.addListener(window, "load", prepareLinks);