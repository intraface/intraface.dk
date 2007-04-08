/**
 * cms
 *
 * Dette javascript lave alle de lækre funktioner på page_edit.php 
 *
 * @author		Lars Olesen <lars@legestue.net>
 */

var cms = {
	init: function() {
		var adminbar = YAHOO.util.Dom.getElementsByClassName("element");

		var max = adminbar.length;

		for (i=0;i<max;i++) {
			currentColor = adminbar[i].style.background;
			adminbar[i].style.border = "1px solid white";
			var firstChildNode = cms.getFirstChild(adminbar[i]);
			firstChildNode.style.display = "none";	
			YAHOO.util.Event.addListener(adminbar[i], "mouseover", cms.lightUp);
			YAHOO.util.Event.addListener(adminbar[i], "mouseout", cms.lightOff);

		}
	},

	//
	// Denne funktion er nødvendig fordi IE og Moz behandler nodes forskelligt:
	// http://www.w3schools.com/dom/prop_element_firstchild.asp
	//
	getFirstChild: function(elm) {
		var x=elm.firstChild;
		while (x.nodeType != 1) {
			x=x.nextSibling;
		}
		return x;
	},
	
	lightUp: function(elm) {
		this.style.border = "1px solid #ccc";
		this.style.background = "#eee";
		var firstChildNode = cms.getFirstChild(this);
		firstChildNode.style.display = "block";	
	},

	lightOff: function(elm) {
		this.style.border = "1px solid white";
		this.style.background = currentColor;		
		var firstChildNode = cms.getFirstChild(this);
		firstChildNode.style.display = "none";	
	}
	
}

YAHOO.util.Event.addListener(window, "load", cms.init);