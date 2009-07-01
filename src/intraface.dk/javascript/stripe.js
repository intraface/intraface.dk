/* http://validweb.nl/artikelen/javascript/better-zebra-tables/ */

/*
	Egentlig bør funktionen bare få en tabel at stribe. Så kan vi jo starte det hele i main.js.
 */

var stripe = function() {
  //var tables = document.getElementsByTagName("table");
	var tables = YAHOO.util.Dom.getElementsByClassName("stripe");  

  for(var x=0;x!=tables.length;x++){
    var table = tables[x];
    if (! table) { return; }
    
    var tbodies = table.getElementsByTagName("tbody");
    
    for (var h = 0; h < tbodies.length; h++) {
      var even = true;
      var trs = tbodies[h].getElementsByTagName("tr");
      
      for (var i = 0; i < trs.length; i++) {
        trs[i].onmouseover=function(){
          this.className += " ruled"; return false
        }
        trs[i].onmouseout=function(){
          this.className = this.className.replace("ruled", ""); return false
        }
        
        if(even)
          trs[i].className += " even";
        
        even = !even;
      }
    }
  }
}

YAHOO.util.Event.addListener(window, "load", stripe);