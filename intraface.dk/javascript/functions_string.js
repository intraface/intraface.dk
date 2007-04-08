String.prototype.trim = function() {
	// skip leading and trailing whitespace
	// and return everything in between
	var x=this;
	x=x.replace(/^\s*(.*)/, "$1");
	x=x.replace(/(.*?)\s*$/, "$1");
	return x;
}
