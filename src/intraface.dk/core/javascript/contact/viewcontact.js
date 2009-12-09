YAHOO.util.Event.addListener(window, "load", viewCustomerInit);

var show = false;
var compatible = (document.getElementById);


function viewCustomerInit() {
	if (!compatible) return;
  return; // nedenstående er flyttet, så det kan ikke oprettes - bør sikkert laves igen med noget AJAX
	var o = document.getElementById('createmessage');
	if (o) {
		o.onclick = toggleMessageWindow;
	}

}

function toggleMessageWindow() {

	if (document.getElementById) {
		if (show == false) {
			document.getElementById('message_form').style.display = 'block';
			document.getElementById('createmessage').style.display = 'none';			
			show = true;
		}
		else {
			document.getElementById('message_form').style.display = 'none';
			document.getElementById('createmessage').style.display = 'inline';
			show = false;			
		}
	}
}