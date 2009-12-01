function focusField(target)
{
	// The form elements that will be tested. Anything with a dot indicates the "type" attribute of the element
	var formElements = ["input.text", "input.checkbox", "input.radio", "select", "textarea"];
	var selectedNode = null;

	// IE's selection method
	if (typeof document.selection != "undefined" && document.selection != null && typeof window.opera == "undefined")
	{
		var theSelection = document.selection;
		var textRange = document.selection.createRange();

		selectedNode = textRange.parentElement();
	}
	// W3 selection method. Currently only Mozilla & Safari support it. However, neither of them support ranges inside form objects, so this part is redundant. Merely included in case they decide to include support in the future
	else if (typeof window.getSelection != "undefined")
	{
		var theSelection = window.getSelection();

		// The Safari way to get the node that a selection starts in
		if (typeof theSelection.baseNode != "undefined")
		{
			selectedNode = theSelection.baseNode;
		}
		// The Mozilla way to get the node that a selection starts in
		else if (typeof theSelection.getRangeAt != "undefined" && theSelection.rangeCount > 0)
		{
			selectedNode = theSelection.getRangeAt(0).startContainer;
		}
	}

	// If a selected node was found above, check whether it's a selection inside one of the specified form element types
	if (selectedNode != null)
	{
		for (var i = 0; i < formElements.length; i++)
		{
			if (selectedNode.nodeName.toLowerCase() == formElements[i].replace(/([^.]*)\..*/, "$1"))
			{
				return false;
			}
		}
	}

	var forms = document.forms;

	// Do a check of each form element on the page. If one of them has a value, do not focus
	for (var i = 0; i < forms.length; i++)
	{
		var formElements = forms[i];

		for (var j = 0; j < formElements.length; j++)
		{
			if (formElements[j].getAttribute("type") == "checkbox" || formElements[j].getAttribute("type") == "radio")
			{
				if (formElements[j].checked != formElements[j].defaultChecked)
				{
					return false;
				}
			}
			else
			{
				if (typeof formElements[j].defaultValue != "undefined" && formElements[j].value != formElements[j].defaultValue)
				{
					return false;
				}
			}
		}
	}

	// If no form elements were found to be focused -- or with values -- go ahead and focus
	target.focus();

	return false;
}