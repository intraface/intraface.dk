/*
addEvent(window, "load", initConfirmBoxes);

var compatible = document.getElementById;



function initConfirmBoxes() {
	applyConfirmBoxes("confirm");
	applyConfirmBoxes("delete");
}


function applyConfirmBoxes(sClass) {
	var oA = getElementsByClass(sClass);
	if (!oA) return;
	var n = oA.length;
	for (var i=0; i<n; i++){
	  return confirmBox(oA[i]);
	}
	
}

function confirmBox(elm) {
	var sTitle = elm.title;
	elm.onclick =  function() {
			if (!sTitle) {
				return confirm("Are you sure?");
			}
			else {
				return confirm(sTitle);
			}
		}
}
*/