var list = {
	init: function() {
		var oInput = YAHOO.util.Dom.get('date-from');
		if (oInput) {
			YAHOO.util.Event.addListener(oInput, "click", list.showCalender);
		}
		
	},
	
	showCalender: function() {
		var calender = new YAHOO.widget.Calendar("calender", "calender");
		calender.render();
	}

}

/*
	LIGE NU KOPIERER KALENDEREN SIG SELV, NÅR MAN KLIKKER PÅ DEN. - og det er ikke lavet så den smider datoerne ind i datofelterne.
*/

//YAHOO.util.Event.addListener(window, "load", list.init);