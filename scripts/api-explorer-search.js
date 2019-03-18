addFilterBox({
    suffix: "-apiexplorer-panel",
    target: {
        selector: "#tracy-debug-panel-ApiExplorerPanel .tracy-inner",
        items: "tbody tr"
    },
    wrapper: {
        tag: "div",
        attrs: {
            id: "tracyApiExplorerFilterBoxWrap",
            class: "tracy-filterbox-wrap tracy-filterbox-titlebar-wrap"
        }
    },
    input: {
        attrs: {
            placeholder: "Find..."
        }
    },
    addTo: {
        selector: "#tracy-debug-panel-ApiExplorerPanel .tracy-icons",
        position: "before"
    },
    inputDelay: 500,
    highlight: {
        style: "background: #ff9; color: #125eae;",
        minChar: 2
    },
    displays: {
        counter: {
            tag: "p",
            addTo: {
                selector: "#tracyApiExplorerFilterBoxWrap",
                position: "append"
            },
            attrs: {
                class: "tracy-filterbox-counter"
            },
            text: function () {
	            var text = "";

	            if(this.getFilter() !== "") {
		            var matches = this.countVisible(),
		            	total = this.countTotal();

	                text = matches ? "<span>" + matches + "</span>/" + total : "No match";
	            }

	            return text;
            }
        },
        clearButton: {
            tag: "span",
            addTo: {
                selector: "#tracyApiExplorerFilterBoxWrap",
                position: "append"
            },
            attrs: {
                class: "tracy-filterbox-clear",
                onclick: "var input = this.parentElement.querySelector('input'); input.getFilterBox().clearFilterBox(); input.focus();"
            },
            text: function () {
                return this.getFilter() ? "&times;" : "";
            }
        }
    },
    callbacks: {
        onReady: function () {
            var $target = this.getTarget(),
                $input = this.getInput();

            $input.addEventListener("input", function() {
                $target.setAttribute("data-filterbox-active", "1");
            });
        },
        afterFilter: function () {
            var filter = (this.getFilter() || "").trim(),
                visibleSelector = this.getVisibleSelector(),
                $target = document.querySelector("#tracy-debug-panel-ApiExplorerPanel .tracy-inner"),
                $itemsToHide,
                $foundFiles,
                $file,
                $parents,
                displayAttr = "data-filterbox-display",
                visibleMode = "table-row",
                hiddenMode = "none";

            if(filter === "") {
                var $itemsToClean = $target.querySelectorAll("[" + displayAttr + "]");

                $target.setAttribute("data-filterbox-active", "0");

                for(var i = 0; i < $itemsToClean.length; i++) {
                    $itemsToClean[i].removeAttribute(displayAttr);
                }

                return false;
            }

            /*$itemsToHide = $target.querySelectorAll("");

            for(var i = 0; i < $itemsToHide.length; i++) {
                $itemsToHide[i].setAttribute(displayAttr, hiddenMode);
            }*/

            $foundFiles = $target.querySelectorAll(visibleSelector);

            for(var j = 0; j < $foundFiles.length; j++) {
                $file = $foundFiles[j];
                $parents = getParentsUntil($file, "#tracy-debug-panel-ApiExplorerPanel .tracy-inner");

                $file.setAttribute(displayAttr, visibleMode);

                for(var k = 0; k < $parents.length; k++) {
                    $parents[k].setAttribute(displayAttr, visibleMode);
                }
            }
            // reposition panel so that if it's wider after filtering (expanding results), it won't be off the screen
            window.Tracy.Debug.panels['tracy-debug-panel-ApiExplorerPanel'].reposition();
        }
    }
});

/*!
* Get all of an element's parent elements up the DOM tree until a matching parent is found
* (c) 2019 Chris Ferdinandi, MIT License, https://gomakethings.com
* @param  {Node}   elem     The element
* @param  {String} parent   The selector for the parent to stop at
* @param  {String} filter   The selector to filter against [optional]
* @return {Array}           The parent elements
*/
var getParentsUntil = function (elem, parent, filter) {
// Setup parents array
var parents = [];

// Get matching parent elements
while (elem && elem !== document) {
    // If there's a parent and the element matches, break
    if (parent) {
        if (elem.matches(parent)) break;
    }

    // If there's a filter and the element matches, push it to the array
    if (filter) {
        if (elem.matches(filter)) {
            parents.push(elem);
        }
        continue;
    }

    // Otherwise, just add it to the array
    parents.push(elem);
    elem = elem.parentNode;
}

return parents;
};

/**
* Element.matches() polyfill (simple version)
* https://developer.mozilla.org/en-US/docs/Web/API/Element/matches#Polyfill
*/
if (!Element.prototype.matches) {
Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}
