/*
	Følgende kræver at confirmBox findes.
	
	http://the-stickman.com/include/multifile.js
	How to make objectoriented javascript :) Kan i hvert fald bruges til Ajax Message
*/
var compatible = document.getElementById && document.createElement;

function AjaxMessage(in_node, before_node) {
	var oMessage = document.getElementById("ajax_message");
	if (!oMessage) {
		oMessage = document.createElement("DIV");
		oMessage.setAttribute("id","ajax_message");
	}
	in_node.insertBefore(oMessage, before_node);
  return oMessage;	
}

function deleteObject() {
	var o = this;
	if (!o) return;

	var oXMLHttp = XMLHttp();
	if (!oXMLHttp) {
		return confirmbox(o);
	}
	oXMLHttp.open("get", "/modules/product/index.php?delete="+o.id.substring(6), true);
	oXMLHttp.setRequestHeader('Accept','message/x-jl-formresult');
	oXMLHttp.onreadystatechange= function () { 
		if (oXMLHttp.readyState == 4) {
			if(oXMLHttp.status == 200) {
				var oBody = document.getElementById("maincontent");
				var oForm = document.getElementById("product_table");
         
				var oMessage = AjaxMessage(oBody, oForm);
      
      if (oXMLHttp.responseText > 0) {
					oMessage.setAttribute("class","success");
					oMessage.innerHTML = 'Slettet. <a href="index.php?use_stored=true&undelete='+o.id.substring(6)+'">Fortryd</a>.';
					// dette skal lige finpudses, så det er let at finde ud af hvem, der skal slettes
					var deleteNode = o.parentNode.parentNode.parentNode;
					var removedNode = deleteNode.removeChild(o.parentNode.parentNode);
				}
				else {
					// det kunne absolut overvejes om ikke man skulle return true. 
					// så var man fri for at lave fejlmeddelelser.
					oMessage.setAttribute("class","failure");				
					oMessage.innerHTML = 'Ikke slettet. Denne bug er sikkert oprettet i et andet intranet.';
				}
				document.location.href = "#ajax_message";
      window.setTimeout(removeMessage, 15000);
			}
		}
	}	
  oXMLHttp.send(null);
	return false;
}

function prepareDelete() {
	if (!compatible) return;
  
  var node = document.getElementById("maincontent");
  
	var oDelete = getElementsByClass("ajaxdelete", node);
	var n = oDelete.length;
	for (var i=0;i<n;i++){
    oDelete[i].onclick = deleteObject;
	}
}

function removeMessage() {
	var oContent = document.getElementById('maincontent');
  var oMessage = document.getElementById('ajax_message');
  if (!oContent) return;
  if (!oMessage) return;
  oContent.removeChild(oMessage);
}


addEvent(window, "load", prepareDelete);
 
