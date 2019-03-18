addFilterBox({
	suffix: "-phpinfo-panel",
    target: {
        selector: "#phpinfoBody",
        items: ".phpinfo-section tbody tr:not(.h)"
    },
    wrapper: {
        tag: "div",
        attrs: {
            id: "tracyPhpInfoFilterBoxWrap",
            class: "tracy-filterbox-wrap tracy-filterbox-titlebar-wrap"
        }
    },
    input: {
        attrs: {
            placeholder: "Find..."
        }
    },
    addTo: {
        selector: "#tracy-debug-panel-PhpInfoPanel .tracy-icons",
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
                selector: "#tracyPhpInfoFilterBoxWrap",
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
                selector: "#tracyPhpInfoFilterBoxWrap",
                position: "append"
            },
            attrs: {
                class: "tracy-filterbox-clear",
                onclick: "var input = this.parentElement.querySelector('input'); input.getFilterBox().clearFilterBox(); input.focus(); window.Tracy.Debug.panels['tracy-debug-panel-PhpInfoPanel'].reposition();"
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

            // modify panel markup
            var $firstSection = document.querySelector("#phpinfoBody .phpinfo-section"),
            	$tablesToMove = $firstSection.querySelectorAll("table"),
            	$divToRemove = $firstSection.querySelector("div.center"),
            	tableCount = $tablesToMove.length;

            if(tableCount) {
	            for(var i = 1; i < tableCount; i++) {
					$firstSection.appendChild($tablesToMove[i]);
	            }
	            $divToRemove.parentElement.removeChild($divToRemove);
            }
        },
        afterFilter: function () {
            window.Tracy.Debug.panels['tracy-debug-panel-PhpInfoPanel'].reposition();
            var filter = (this.getFilter() || "").trim(),
                visibleSelector = this.getVisibleSelector(),
                $target = document.querySelector("#phpinfoBody"),
                $itemsToHide,
                $foundItems,
                $item,
                $parents,
                $sections = $target.querySelectorAll(".phpinfo-section"),
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

            $itemsToHide = $target.querySelectorAll("h1, h2, table, tbody tr:not(.h)");

            for(var i = 0; i < $itemsToHide.length; i++) {
                $itemsToHide[i].setAttribute(displayAttr, hiddenMode);
            }

            $foundItems = $target.querySelectorAll(visibleSelector);

            for(var j = 0; j < $foundItems.length; j++) {
                $item = $foundItems[j];
                $parents = getParentsUntil($item, "#phpinfoBody");

                $item.setAttribute(displayAttr, visibleMode);

                for(var k = 0; k < $parents.length; k++) {
	                var $parent = $parents[k],
	                	$previousElement;

					if($parent.getAttribute(displayAttr) === "table") {
						continue;
					}

	                $previousElement = $parent.previousElementSibling;

	                if($parent.tagName === "TABLE") {
		                $parent.setAttribute(displayAttr, "table");
	                }

	                if($previousElement && $previousElement.tagName === "H2") {
		                $previousElement.setAttribute(displayAttr, "block");
					}
                }
            }

			for(var j = 0; j < $sections.length; j++) {
				var $section = $sections[j];

	            if($section.querySelector("table:not([" + displayAttr + "='" + hiddenMode + "'])")) {
		            $section.querySelector("h1").setAttribute(displayAttr, "block");
				}
            }
            window.Tracy.Debug.panels['tracy-debug-panel-PhpInfoPanel'].reposition();
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
