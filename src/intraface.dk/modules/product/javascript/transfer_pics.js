/**
 * Bruger Ajax til at overføre billeder
 */

var compatible = document.getElementById;

window.onload = function() {
	if (!compatible) return;
  var o = document.getElementById("transfer_pics");
	if (!o) return true;
	o.onclick = transferPics;
}

function transferPics() {
	if (!compatible) return true;
  var oXMLHttp = XMLHttp();
	if (!oXMLHttp) return true;

  var contentViewer = document.getElementById("contentViewer");
	
	if (!contentViewer) return true;	

  oXMLHttp.open("GET", "/modules/product/transfer_pics.php?action=transfer", true);
	oXMLHttp.setRequestHeader('Accept','message/x-jl-formresult');
		 
  oXMLHttp.onreadystatechange= function () { 
    if (oXMLHttp.readyState == 4) {
      if(oXMLHttp.status == 200) {
        if (oXMLHttp.responseText == 1) {
          contentViewer.innerHTML = "<strong id='fade' class='fade'>Billederne er overført</strong>";
  			}
      }
    }
    else {
      contentViewer.innerHTML = "<strong style='color:green;'>Overfører billeder...<strong>";
    }
  }	
  oXMLHttp.send(null);  	
	return false;
}