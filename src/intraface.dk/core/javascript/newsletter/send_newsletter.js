/**
 * Bruger Ajax til at overføre billeder
 */

YAHOO.util.Event.addListener(window, "load", initNewsletter);
 
var compatible = document.getElementById;

function initNewsletter() {
	if (!compatible) return;
  var o = document.getElementsByTagName('td');
	var n = o.length;
	for (var i=0;i<n;i++){
		if (!o[i]) continue;
    if(o[i].id.indexOf("letter")!=-1) {
		  sendNewsletter(o[i]);
    }
	}
}

function sendNewsletter(o) {
	if (!compatible) return true;
  var oXMLHttp = XMLHttp();
	if (!oXMLHttp) return true;

  var contentViewer = document.getElementById("letter"+o.id.substring(6));

	if (!contentViewer) return true;	

	var oPost = "action=send&id="+o.id.substring(6)+"&number=200";
  oXMLHttp.open("post", "/modules/newsletter/send.php?letter="+contentViewer.id.substring(6), true);
	oXMLHttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded;');
	oXMLHttp.setRequestHeader('Content-Length', oPost.length);	
	oXMLHttp.setRequestHeader('Accept','message/x-jl-formresult');
		 
  oXMLHttp.onreadystatechange= function () { 

    if (oXMLHttp.readyState == 4) {

      if(oXMLHttp.status == 200) {
        if (oXMLHttp.responseText > 0) {

          contentViewer.innerHTML = "Sendt: " + oXMLHttp.responseText + "%";
  			}
      }
    }
    else {
      contentViewer.innerHTML = "Sender";
    }
  }	
  oXMLHttp.send(oPost);  	
	return false;
}