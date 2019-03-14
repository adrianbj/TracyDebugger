addFilterBox({
        target: {
            selector: ".tracy-file-tree",
            items: "li.tft-f"
        },
        input: {
            attrs: {
                id: "tracyFileEditorFilterBoxInput",
                placeholder: "Find file...",
                style: "width: 290px !important;"
            }
        },
        wrapper: {
            tag: "div",
            attrs: {
                id: "tracyFileEditorFilterBoxWrap",
                style: "padding-bottom: 10px;"
            }
        },
        addTo: {
            selector: "#tracyFoldersFiles",
            position: "before"
        },
        //inputDelay: 500,
        highlight: {
            style: "background: #ff9; color: #125eae;",
            minChar: 2
        },
        displays: {
            noresults: {
                tag: "p",
                addTo: {
                    selector: "#tracyFileEditorFilterBoxWrap",
                    position: "after"
                },
                attrs: {
                    class: "tracyFileEditorFilterBoxNoResults",
                    style: "font-style: italic !important; margin: 0 !important; font-size: 12px !important;"
                },
                text: function () {
                    return !this.countVisible() ? 'Sorry, no match for "' + this.getFilter() + '".' : '';
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
            onEnter: function () {
                var $file = this.getFirstVisibleItem();

                if($file) {
                    $file.querySelector("a").click();
                }

                return false;
            },
            afterFilter: function () {
                var filter = (this.getFilter() || "").trim(),
                    visibleSelector = this.getVisibleSelector(),
                    terms,
                    $fileTree = document.querySelector(".tracy-file-tree"),
                    $fileTreeItemsToHide,
                    $foundFiles,
                    $file,
                    $parents,
                    visibleClass = "filterbox-visible",
                    hiddenClass = "filterbox-hidden";

                if(filter === "") {
                    $fileTree.setAttribute("data-filterbox-active", "0");
                    return false;
                }

                $fileTreeItemsToHide = $fileTree.querySelectorAll("ul, li");

                for(var i = 0; i < $fileTreeItemsToHide.length; i++) {
                        $fileTreeItemsToHide[i].classList.remove(visibleClass);
                        $fileTreeItemsToHide[i].classList.add(hiddenClass);
                }

                $foundFiles = $fileTree.querySelectorAll(visibleSelector);

                for(var j = 0; j < $foundFiles.length; j++) {
                    $file = $foundFiles[j];

                    $file.classList.add(visibleClass);

                    $parents = getParentsUntil($file, ".tracy-file-tree");

                    for(var k = 0; k < $parents.length; k++) {
                        $parents[k].classList.add(visibleClass);
                    }
                }
            }
        }
    }
);



/*!
 * Get all of an element"s parent elements up the DOM tree until a matching parent is found
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
		// If there"s a parent and the element matches, break
		if (parent) {
			if (elem.matches(parent)) break;
		}

		// If there"s a filter and the element matches, push it to the array
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
