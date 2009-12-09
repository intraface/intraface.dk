/**
 * Javascript til todo
 *
 * Jeg synes at vi skal namespace vores javascript. Derved undgår vi en masse
 * dobbelte init'er mv. Desuden synes jeg at det så giver mening blot at have
 * et enkelt javascript til de forskellige moduler. Det vil gøre det noget
 * lettere at finde rundt i.
 *
 * init: skal indeholde et tjek på om javascriptet er kompatibelt med metoderne.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

var todo = {
    init: function() {
        var compatible = document.getElementById && document.createElement;
        if (!compatible) return;

        // todo.php
        if (document.getElementById("new_item_form")) {
            todo.showFormField(document.getElementById("new_item_form"), "Add item");
        }
          if (document.getElementById("email_list_form")) {
            todo.showFormField(document.getElementById("email_list_form"), "Send liste til e-mail");
        }
        // edit_todo.php
        var links = document.getElementsBySelector("fieldset#todolist div a");
        if (links) {
            var n = links.length;
            for (var i= 0; i<n; i++) {
                YAHOO.util.Event.addListener(links[i], "click", function(elm) {
                    this.parentNode.parentNode.removeChild(this.parentNode);
                });
            }
        }
    },

    showFormField: function(elm, text) {
        if (!elm) return;
        var cloneForm = elm.cloneNode(true);

        var pContainer = document.createElement("p");
        var a = document.createElement("a");
        a.appendChild(document.createTextNode(text));
        a.href = "#";
        YAHOO.util.Event.addListener(a, "click", function() {
            pContainer.parentNode.replaceChild(cloneForm, pContainer);
        });

        pContainer.appendChild(a);
        elm.parentNode.replaceChild(pContainer, elm);
    }
}

YAHOO.util.Event.addListener(window, "load", todo.init);