addFilterBox({
    suffix: "-fileeditor-panel",
    target: {
        selector: "#tracyFileEditorContainer .tracy-file-tree",
        items: "li.tft-f"
    },
    wrapper: {
        tag: "div",
        attrs: {
            id: "tracyFileEditorFilterBoxWrap",
            class: "tracy-filterbox-wrap tracy-filterbox-titlebar-wrap"
        }
    },
    input: {
        attrs: {
            placeholder: "Find file..."
        }
    },
    addTo: {
        selector: "#tracy-debug-panel-FileEditorPanel .tracy-icons",
        position: "before"
    },
    inputDelay: 500,
    suffix: 'file-editor',
    highlight: {
        style: "background: #ff9; color: #125eae;",
        minChar: 2
    },
    displays: {
        counter: {
            tag: "p",
            addTo: {
                selector: "#tracyFileEditorFilterBoxWrap",
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
                selector: "#tracyFileEditorFilterBoxWrap",
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
        onEnter: function () {
            var $file = this.getFirstVisibleItem();

            if($file) {
                $file.querySelector("a").click();
            }

            return false;
        },
        afterFilter: function () {
            var filter = (this.getFilter() || "").trim(),
                matchingSelector = this.getMatchingSelector(filter),
                $target = document.querySelector(".tracy-file-tree"),
                $itemsToHide,
                $foundFiles,
                $file,
                $parents,
                displayAttr = "data-filterbox-display",
                visibleMode = "true",
                hiddenMode = "none";

            if(filter === "") {
                var $itemsToClean = $target.querySelectorAll("[" + displayAttr + "]");

                $target.setAttribute("data-filterbox-active", "0");

                for(var i = 0; i < $itemsToClean.length; i++) {
                    $itemsToClean[i].removeAttribute(displayAttr);
                }

                return false;
            }

            $itemsToHide = $target.querySelectorAll("ul, li");

            for(var i = 0; i < $itemsToHide.length; i++) {
                $itemsToHide[i].setAttribute(displayAttr, hiddenMode);
            }

            $foundFiles = $target.querySelectorAll(matchingSelector);

            for(var j = 0; j < $foundFiles.length; j++) {
                $file = $foundFiles[j];
                $parents = getParentsUntil($file, ".tracy-file-tree");

                $file.setAttribute(displayAttr, visibleMode);

                for(var k = 0; k < $parents.length; k++) {
                    $parents[k].setAttribute(displayAttr, visibleMode);
                }
            }
        }
    }
}
);