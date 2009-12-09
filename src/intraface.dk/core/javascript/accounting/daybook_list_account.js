/**
 * This javascript loops through a table with numbers and create a link of each
 * number. All new elements (the links) get an eventhandler (click). The
 * eventhandler puts the number into an input-field on the page which opened
 * this page.
 *
 * @see http://developer.yahoo.com/ (YUI)
 * @author Lars Olesen <lars@legestue.net>
 */

var account = {
	init: function() {
		if (!document.getElementsByTagName || !document.getElementById || !document.location.search.substring(1)) {
			return;
		}
		this.elm = document.getElementsByTagName('th');
		this.n = this.elm.length;

		for (i=0;i<this.n;i++){
			this.elmText = getInnerText(this.elm[i]);

			var account_number = this.elmText.trim(); // en af vores egne funktioner

			if (!this.elmText) {
				continue;
			}

			this.oA = document.createElement("a");
			this.oA.appendChild(document.createTextNode(account_number));
			this.oA.href="#";

			elm[i].replaceChild(this.oA, this.elm[i].firstChild);

			YAHOO.util.Event.addListener(this.oA, 'click', (function(n) {
				return function() {
					account.write(n);
				};
			})(account_number));
			/*
			YAHOO.util.Event.addListener(this.oA, "click", function() {
				account.write(account_number)
			});
			*/

		}
	},
	write: function(v) {
		window.opener.document.getElementById(document.location.search.substring(1)).value = v;
		window.close();
		window.opener.focus();

	}
}
YAHOO.util.Event.addListener(window, "load", account.init);