function getElementsByClass(className,node,$accumulator) {
	if (node === undefined) {node = document; } 
	if ($accumulator === undefined) { $accumulator = [];} 
	if (node.nodeType == 1 && new RegExp("\\b"+className+"\\b").test(node.className)) { 
		$accumulator.push(node); 
	} 
	for(var chld = node.firstChild;chld;chld=chld.nextSibling) { 
		getElementsByClass(className,chld,$accumulator); 
	} 
	return $accumulator; 
} 
