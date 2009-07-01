var maxChars = {
	// cross-browser event handling for IE5+, NS6 and Mozilla 
	// By Scott Andrew 
	addEvent: function(elm, evType, fn, useCapture) {
		if(elm.addEventListener) {
			elm.addEventListener(evType, fn, useCapture);
			return true;
		} 
		else if(elm.attachEvent) {
			var r = elm.attachEvent('on' + evType, fn);
			return r;
		} 
		else {
			elm['on' + evType] = fn;
		}
	},
	attVal: function(element, attName) {
		return parseInt(element.getAttribute(attName));
	},

	init: function() {
		return;
		if(!document.getElementsByTagName || !document.getElementById) {
			return;
		}

		var forms = document.getElementsByTagName("form");

		if (forms.length == 0) return;		

		for(var i=0;i!=forms.length;i++){

			var form = forms[i];
			if (!form) { 
				return;
			}
    
			// denne skal gennemløbe alle tekstfelter med en maxlength
			// selv oprette et element i label, som laver en tællerdims
			// hente alle textarea og input elementer
			maxChars.elm = document.getElementsByTagName('input');
			
			for (var j = 0; j < maxChars.elm.length; j++) {
				maxChars.j = j;

				maxChars.maxlength = maxChars.attVal(maxChars.elm[maxChars.j], 'maxlength');
				maxChars.limit_span = new Array();
				maxChars.limit_span_text = new Array();				
				maxChars.limit_span[maxChars.j] = document.createElement('span');
				maxChars.limit_span_text[maxChars.j] = document.createTextNode('(' + maxChars.maxlength + ')');
				maxChars.limit_span[maxChars.j].appendChild(maxChars.limit_span_text[maxChars.j]);
				var z = maxChars.elm[maxChars.j].parentNode;
				z.insertBefore(maxChars.limit_span[maxChars.j], maxChars.elm[maxChars.j]);
				
				maxChars.addEvent(maxChars.elm[maxChars.j], 'keyup', maxChars.countlimit, false);
			}
		}
	},

	countlimit: function(e) {
		var placeholder;
		var lengthleft = maxChars.maxlength - maxChars.elm[maxChars.j].value.length;
		
		if(e && e.target) {
			placeholder = e.target;
		}

		if(window.event && window.event.srcElement) {
			placeholder = window.event.srcElement;
		}

		if(!placeholder) {
			return;
		} 
		else if(lengthleft < 0) {
			maxChars.textarea.value = maxChars.elm[maxChars.j].value.substring(0, maxChars.maxlength);
		} 
		else if(lengthleft > 1) {
			maxChars.limit_span[maxChars.j].innerHTML = '<strong>' + lengthleft + '</strong>' + ' characters remaining.';
		} 
		else {
			maxChars.limit_span[maxChars.j].innerHTML = '<strong>' + lengthleft + '</strong>' + ' character remaining.';
		}

	}

}

YAHOO.util.Event.addListener(window, "load", maxChars.init);