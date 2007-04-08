YAHOO.util.Event.addListener(window, "load", init);

var compatible = document.getElementById;

function init() {
	if (!compatible) return;
  var oSearch = document.getElementById("search");
  if(!oSearch) return;
/*
  var oButton = document.getElementById("search_button");
  if(oButton) oButton.style.display="none";
	*/
	var oSelect = document.getElementById("contact_select");
  if (oSelect) oSelect.style.display = "none";   
	/*  
  var oNewButton = document.getElementById("new_contact");
  if(oNewButton) oNewButton.onclick = function() { 
  	var oType = document.getElementById("type_select");
    if (!oType) return false; 
    if (!oType.value) { 
      return false; 
    } 
    else { 
    	return true; 
    } 
  }
  */
  var oForm = document.getElementById("new");
  if (!oForm) return;
  oForm.onsubmit = function() {
	 	var oType = document.getElementById("type_select");
  	if (!oType.value) {
    	var oLabel = document.getElementById("type_label");
     	oType.focus(); 
      if (!oLabel) return;
      oLabel.style.color = "red";
    	return false;
    }
  }

  oSearch.onkeyup = searchContact;
}

// det bør laves på en time i stedet for keyup
function searchContact() {
	var oSearch = document.getElementById("search");
  if (!oSearch) return;
	if(oSearch.value.length < 3) return true;
	
	var oXMLHttp = XMLHttp();
	if (!oXMLHttp) return true;
  
	var oSelect = document.getElementById("contact_select");
  if (!oSelect) return;
  oSelect.style.display = "block";
  
	oXMLHttp.onreadystatechange = function () {
		oSelect.options.length = 0;
		if(oXMLHttp.readyState == 4) {
			if(oXMLHttp.status == 200) {
				if(oXMLHttp.responseText != "") {

					var xmldoc = XMLParser(oXMLHttp.responseText);
					
					var items = xmldoc.getElementsByTagName("contact");
					if (items.length > 0) {
					for (i=0;i<items.length;i++) {
						var name = items[i].getElementsByTagName("name");	
						var id = items[i].getElementsByTagName("id");	
						var entry = new Option(name[0].firstChild.nodeValue, id[0].firstChild.nodeValue, 0, 0);
						oSelect.options[oSelect.options.length] = entry;
					}
          }
          else {
          			var entry = new Option("Ingen fundet...", 1, 0, 0);
							oSelect.options[oSelect.options.length] = entry;      

          }
          
				}
			}
		}
		else {
			//oSelect.innerHTML = '<option value="">Søger...</option>';
			var entry = new Option("Søger...", 1, 0, 0);
			oSelect.options[oSelect.options.length] = entry;      
		}
	}
	

	// if (!search) return true;
	var url = "/modules/debtor/select_contact.php";
	var var_send = "search="+encodeURIComponent(oSearch.value);  
	oXMLHttp.open("POST", url, true);
	oXMLHttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded;');
	oXMLHttp.setRequestHeader('Content-Length', var_send.length);  
	oXMLHttp.setRequestHeader('Accept','message/x-jl-formresult');
	oXMLHttp.send(var_send);
	
	return false;
}	