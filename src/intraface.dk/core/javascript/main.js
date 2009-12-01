var confirmboxes = {

	init: function() {
		// confirmboxes.apply("delete", "a"); /* Removed for K2 system */
		confirmboxes.apply("delete", "input");
		confirmboxes.apply("confirm", "a");	
		confirmboxes.apply("confirm", "input");
	},
	
	apply: function(sClass, sElement) {
		var elements = YAHOO.util.Dom.getElementsByClassName(sClass, sElement, "content");
		if (!elements) return;
		var n = elements.length;
		for (var i=0; i<n;i++) {
			
			var event = YAHOO.util.Event.addListener(elements[i], "click", function(e) {
				// kunne godt lige udvides med at tage title
				if(this.title) {
					var title = this.title;
				} else {
					var title = "Er du sikker?";
				}
				if (!confirm(title)) {
					YAHOO.util.Event.stopEvent(e);
				}
			});		
		}
	}
}


var forms = {
	init: function() {
		if (!document.getElementsByTagName) return;
		forms.appendInputTypeClasses();
	},
	
	appendInputTypeClasses: function() {
	
		var inputs = document.getElementsByTagName('input');
		var inputLen = inputs.length;
		for (i=0;i<inputLen;i++) {
			if (inputs[i].getAttribute('type')) {
				inputs[i].className += ' '+inputs[i].getAttribute('type');
			}
		}
	}	
}


YAHOO.util.Event.addListener(window, "load", confirmboxes.init);
YAHOO.util.Event.addListener(window, "load", forms.init);