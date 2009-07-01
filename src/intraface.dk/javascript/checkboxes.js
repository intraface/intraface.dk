/**
 * This script replaces checkboxes with images
 *
 * Usage:
 * -----
 *
 * 1. Edit the checkboxes.init-function and have the images relect the 
 *    images, you want to put instead of the checkbox. 
 * 2. Put the checkboxes.init in an event-handler
 *
 * Known bugs:
 * ----------
 * - Apparently events are not transferred correctly
 *
 *
 * @author   Lars Olesen <lars@legestue.net>
 * @version  1.0
 * @original http://www.slayeroffice.com/code/custom_checkbox/
 *
 * This work is licensed under the Creative Commons License by-sa
 * http://creativecommons.org/licenses/by-sa/2.5/
 *
 * This basically means that you can use the script for everything, 
 * as long as you share on the same terms.
 *
 * Please tell me if you improve this script. 
 */


var checkboxes = {
	init: function() {
		checkboxes.img_off = "/images/icons/silk/cancel.png";
		checkboxes.img_on = "/images/icons/silk/accept.png";		
		checkboxes.canCreate();
	},
	canCreate: function() {
		// make sure the browser has images turned on. If they are, so_createCustomCheckBoxes will
		// fire when this small test image loads. otherwise, the user will get the hard-coded checkboxes
		testImage = document.body.appendChild(document.createElement("img"));

		// MSIE will cache the test image, causing it to not fire the onload event the next time the
		// page is hit. The parameter on the end will prevent this.
		testImage.src = "/images/icons/silk/bullet_arrow_up.png?" + new Date().valueOf();
		testImage.id = "so_testImage";
		testImage.onload = checkboxes.createCustomCheckBoxes;
	},

	createCustomCheckBoxes:function() {
		// bail out is this is an older browser
		if(!document.getElementById) return;
		// remove our test image from the DOM
		document.body.removeChild(document.getElementById("so_testImage"));
		// an array of applicable events that we'll need to carry over to our custom checkbox
		//events = new Array("onfocus", "onblur", "onselect", "onchange", "onclick", "ondblclick", "onmousedown", "onmouseup", "onmouseover", "onmousemove", "onmouseout", "onkeypress", "onkeydown", "onkeyup");
		// a reference var to all the forms in the document

		frm = document.getElementsByTagName("form");
		// loop over the length of the forms in the document
		for(i=0;i<frm.length;i++) {
			// reference to the elements of the form
			c = frm[i].elements;
			// loop over the length of those elements
			for(j=0;j<c.length;j++) {
				// if this element is a checkbox, do our thing

				if(c[j].getAttribute("type") == "checkbox") {
					// hide the original checkbox
					c[j].style.position = "absolute";
					c[j].style.left = "-9000px";
					//c[j].style.visibility = "hidden";
					// create the replacement image
					n = document.createElement("img");
					//n.setAttribute("class","chk "+c[j].className);
				      YAHOO.util.Dom.addClass(n, "new-checkbox");
					
					// check if the corresponding checkbox is checked or not. set the
					// status of the image accordingly
					if(c[j].checked == false) {
						n.setAttribute("src",checkboxes.img_off);
						n.setAttribute("title","click here to select this option.");
						n.setAttribute("alt","click here to select this option.");
					} else {
						n.setAttribute("src",checkboxes.img_on);
						n.setAttribute("title","click here to deselect this option.");
						n.setAttribute("alt","click here to deselect this option.");
					}
					// there are several pieces of data we'll need to know later.
					// assign them as attributes of the image we've created
					// first - the name of the corresponding checkbox
					n.xid = c[j].getAttribute("name");
					// next, the index of the FORM element so we'll know which form object to access later

					n.frmIndex = i;
					// assign the onclick event to the image
					n.onclick = function() { checkboxes.toggleCheckBox(this,0);return false; }
					// insert the image into the DOM
					c[j].parentNode.insertBefore(n,c[j].nextSibling)
					// this attribute is a bit of a hack - we need to know in the event of a label click (for browsers that support it)
					// which image we need turn on or off. So, we set the image as an attribute!
					c[j].objRef = n;

					/*
					listeners = YAHOO.util.Event.getListeners(c[j]);
					
					for(e=0;e<listeners.length;e++) {
						YAHOO.util.Event.addListener(n, "click", listeners[e].fn);
					}	
					*/				
					/*
					// assign the checkbox objects event handlers to its replacement image
					for(e=0;e<events.length;e++) if(eval('c[j].' +events[e])) {

						eval('n.' + events[e] + '= c[j].' + events[e]);
						//YAHOO.util.Event.addListener(window, events[e], eval('c[j].' + events[e]));
					}
					*/
					// append our onchange event handler to any existing ones.
					fn = c[j].onchange;
					if(typeof(fn) == "function") {
						c[j].onchange = function() { fn(); checkboxes.toggleCheckBox(this.objRef,1); return false; }
					} else {
						c[j].onchange = function () { checkboxes.toggleCheckBox(this.objRef,1); return false; }
					}
				}
			}
		}
	},

	toggleCheckBox:function(imgObj,caller) {
		// if caller is 1, this method has been called from the onchange event of the checkbox, which means
		// t	he user has clicked the label element. Dont change the checked status of the checkbox in this instance
		// or we'll set it to the opposite of what the user wants. caller is 0 if coming from the onclick event of the image
	
		// reference to the form object
		formObj = document.forms[imgObj.frmIndex];
		// the name of the checkbox we're changing
		objName = imgObj.xid;
		// change the checked status of the checkbox if coming from the onclick of the image
		if(!caller)formObj.elements[objName].checked = !formObj.elements[objName].checked?true:false;
		// finally, update the image to reflect the current state of the checkbox.
		if(imgObj.src.indexOf(checkboxes.img_on)>-1) {
			imgObj.setAttribute("src",checkboxes.img_off);
			imgObj.setAttribute("title","click here to select this option.");
			imgObj.setAttribute("alt","click here to select this option.");
		} else {
			imgObj.setAttribute("src",checkboxes.img_on);
			imgObj.setAttribute("title","click here to deselect this option.");
			imgObj.setAttribute("alt","click here to deselect this option.");
		}
	}

}

//YAHOO.util.Event.addListener(window, "load", checkboxes.init);