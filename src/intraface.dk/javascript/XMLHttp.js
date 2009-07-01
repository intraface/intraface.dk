/* http://www.higgle.com/ */

function XMLHttp()
{
	var o = false;
	if (typeof XMLHttpRequest != 'undefined') 
	{
		o = new XMLHttpRequest();
	} 
	else 
	{
		try 
		{
			o = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) 
		{
			try 
			{
				o = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (E) 
			{
				o = false;
			}
		}
	}
	return o;
}

function XMLParser(xmlString) {
	if (document.implementation.createDocument){
		// Mozilla, create a new DOMParser
		var parser = new DOMParser();
		doc = parser.parseFromString(xmlString, "text/xml");
		// doc.onload = execXMLParser;
	} 
	else if (window.ActiveXObject){
		// IE, create a new XML document using ActiveX
		// and use loadXML as a DOM parser.
		doc = new ActiveXObject("Microsoft.XMLDOM")
		doc.async="false";
		doc.loadXML(xmlString); 
		// doc.onreadystatechange = function () {
		// 	if (doc.readyState == 4) execXMLParser()
		// }
		    
	}
	else {
		alert("Du skal benytte en browser der understøtter XMLDOM");
		return;
	}
	return doc;
}