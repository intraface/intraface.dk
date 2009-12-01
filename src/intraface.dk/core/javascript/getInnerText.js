function getInnerText(el) {
	if (typeof el == "string") { return el; }
	if (typeof el == "undefined") { return el; };
	if (el.innerText) { return el.innerText;	} //Not needed but it is faster 

	var str = "";
	var cs = el.childNodes;

	for (var i = 0; i < cs.length; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				str += getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				str += cs[i].nodeValue;
				break;
		}
	}

	return str;
}