addFilterBox({
    suffix: "-fileeditor-panel",
    target: {
        selector: "#tracyFileEditorContainer .tracy-file-tree",
        items: "li.tft-f"
    },
    wrapper: {
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
        selector: "#tracy-debug-panel-FileEditorPanel .tracy-inner",
        position: "append"
    },
    inputDelay: 500,
    keyNav: true,
    highlight: {
        style: "background: #ff9; color: #125eae;",
        minChar: 2
    },
    displays: setupTracyPanelFilterBoxDisplays,
    callbacks: {
        onReady: function () {
            var $target = this.getTarget(),
                $input = this.getInput();

            $input.addEventListener("input", function() {
                $target.setAttribute("data-filterbox-active", "1");
            });
        },
        onEnter: function (e) {
            e.preventDefault();
            var $item = this.getSelectedItem();
            $item && $item.querySelector("a").click();
            return false;
        },
        afterFilter: function () {
            var filter = this.getFilter(),
                matchingSelector = this.isInvertFilter() ? this.getHiddenSelector(this.getInvertFilter()) : this.getVisibleSelector(filter),
                $target = this.getTarget(),
                $itemsToHide,
                $foundItems,
                $item,
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

            try {
                 $foundItems = $target.querySelectorAll(matchingSelector);

                for(var j = 0; j < $foundItems.length; j++) {
                    $item = $foundItems[j];
                    $parents = getParentsUntil($item, ".tracy-file-tree");

                    $item.setAttribute(displayAttr, visibleMode);

                    for(var k = 0; k < $parents.length; k++) {
                        $parents[k].setAttribute(displayAttr, visibleMode);
                    }
                }
            } catch (e) {}
        }
    }
});