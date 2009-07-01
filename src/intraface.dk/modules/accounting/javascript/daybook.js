/**
 *
 * @author Lars Olesen <lars@legestue.net>
 */

var daybook = {

	init: function() {
		// tjekker om scriptet er kompatibelt med browseren
		var compatible = (document.getElementById && document.createElement);
		if (!compatible) {
			return;
		}

		// sætter fokus til det første felt
		var o = document.getElementById("date");
		if (o) {
			focusField(o);
		}

		// laver hjælpetekst, hvis man klikker mellem felterne i stedet for at bruge tab
		var o = document.getElementById("voucher_number");
		if (o) {
			YAHOO.util.Event.addListener(o, "click", daybook.help_tab);
		}

		/*
		// laver hjælpetekst, hvis man klikker på Gem i stedet for at at trykke på enter
		// men vi skal lige være ikker på at det er et museklik
		var oSubmit = document.getElementById("submit");
		if (oSubmit) {
			YAHOO.util.Event.addListener(oSubmit, "click", daybook.help_enter);
		}
		*/

		// laver link til debetkontofeltet
		var oDebet = document.getElementById("debet_account_number");
		if (oDebet) {
			var o = document.getElementById('debet_account_open');
			YAHOO.util.Event.addListener(o, "click", function(e) {
				daybook.account_window_open('debet_account_number');
				YAHOO.util.Event.stopEvent(e);
				return false;
			});
			YAHOO.util.Event.addListener(oDebet, "blur", function() {
				daybook.find_account('debet');
			});

		}

	  	// laver link til kreditkontofeltet
		var oCredit = document.getElementById("credit_account_number");
		if (oCredit) {
			var o = document.getElementById("credit_account_open");
			YAHOO.util.Event.addListener(o, "click", function(e) {
				daybook.account_window_open('credit_account_number');
				YAHOO.util.Event.stopEvent(e);
				return false;
			});
			YAHOO.util.Event.addListener(oCredit, "blur", function() {
				daybook.find_account('credit');
			});

		}

		// denne kunne sagtens laves mere generelt, så den gjaldt for alle links, der havde fx class hide
		var sUrl = YAHOO.util.Dom.get('accounting-cheatsheet-link');
		if (!sUrl) return;
		YAHOO.util.Event.addListener(sUrl, "click", function(e) {
			var request = YAHOO.util.Connect.asyncRequest('GET', sUrl + "&ajax=true", {
				success: function(o) {
					if (o.responseText == '1') {
						//sUrl.innerHTML = 'Næste gang du loader siden er denne boks skjult';
						YAHOO.util.Dom.get('accounting-cheatsheet').style.display = "none";
						/*
						var myAnim = new YAHOO.util.Anim('accounting-cheatsheet', {
							width: {to: 0},
							height: {to: 0},
							fontSize: {from: 100, to: 0, unit: '%'},
							opacity: { to: 0.0 } }, 1, YAHOO.util.Easing.easeOut);
						myAnim.animate();
						*/
					}
				}

			});
			if (request) {
				YAHOO.util.Event.stopEvent(e);
			}
		});
	},

	find_account: function(inputField) {
		var account = document.getElementById(inputField + '_account_number');
		var elem = document.getElementById(inputField + '_account_name');

		if (document.hasChildNodes && document.removeChild && elem) {
			while (elem.hasChildNodes()) {
				elem.removeChild(elem.lastChild);
			}
			elem.innerHTML = "&nbsp;";
		}

		var xmlhttp = XMLHttp();
  		if (xmlhttp && account && elem) {
			url = "ajax_find_account.php?s="+account.value;
			if (!xmlhttp) return;
			xmlhttp.open("GET",url,true);
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState==4) {
					var sp = document.createElement('span');
					sp.style.fontSize = "0.8em";
					var text = document.createTextNode(xmlhttp.responseText);
					sp.appendChild(text);
					elem.appendChild(sp);
				}
			}

			xmlhttp.setRequestHeader('Accept','message/x-formresult');
			xmlhttp.send(null);
			return false;
		}
	},
	account_window_open: function(account_id) {
		var accountlist = window.open("daybook_list_accounts.php?"+account_id, "accountlist", "height=600, width=350, scrollbars=yes");
		if (!accountlist) return;
		accountlist.window.focus();

        // @todo make sure this closes the window again
		window.onunload = function() {
			if (accountlist && accountlist.open && !accountlist.closed) {
				accountlist.close();
			}
		}

		return false;

	},

	help_tab: function() {
		var oForm = document.getElementById("accounting-form-state");
		if (!oForm) return;
		var oMessage = document.getElementById("advice");
		if (!oMessage) {
			oMessage = document.createElement("DIV");
			oMessage.setAttribute("id","advice");
			oMessage.setAttribute("class","advice");
		}
		while (oMessage.hasChildNodes()) {
			oMessage.removeChild(oMessage.lastChild);
		}
		var text = document.createTextNode("Det er lettere at trykke på tabulator-tasten, når du vil skifte felt");
		oMessage.appendChild(text);
		oForm.appendChild(oMessage);

	},

	help_enter: function() {
		var oForm = document.getElementById("accounting-form-state");
		if (!oForm) return;
		var oMessage = document.getElementById("advice");
		if (!oMessage) {
			oMessage = document.createElement("DIV");
			oMessage.setAttribute("id","advice");
			oMessage.setAttribute("class","advice");
		}
		while (oMessage.hasChildNodes()) {
			oMessage.removeChild(oMessage.lastChild);
		}
		var text = document.createTextNode("Det er lettere at trykke på enter-tasten, når du vil gemme en post");
		oMessage.appendChild(text);
		oForm.appendChild(oMessage);
	},

	hide: function() {

	}
}

YAHOO.util.Event.addListener(window, "load", daybook.init);
