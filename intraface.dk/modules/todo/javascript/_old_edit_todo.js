YAHOO.util.Event.addListener(window, "load", initTodo);

var compatible = document.getElementById && document.createElement;

function initTodo() {
	var myrules = {
		'fieldset#todolist div a' : function(el){
			el.onclick = function(){
				this.parentNode.parentNode.removeChild(this.parentNode);
			}
		}
	};

	Behaviour.register(myrules);
}