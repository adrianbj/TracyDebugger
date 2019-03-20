addFilterBox({
    suffix: "-phpinfo-panel",
    target: {
        selector: "#phpinfoBody",
        items: ".phpinfo-section tbody tr:not(.h)"
    },
    wrapper: {
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
    displays: setupTracyPanelFilterBoxDisplays,
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
            var filter = this.getFilter(),
                matchingSelector = this.isInvertFilter() ? this.getHiddenSelector(this.getInvertFilter()) : this.getVisibleSelector(filter),
                $target = this.getTarget(),
                $itemsToHide,
                $foundItems,
                $item,
                $parents,
                $sections = $target.querySelectorAll(".phpinfo-section"),
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

            $itemsToHide = $target.querySelectorAll("h1, h2, table, tbody tr:not(.h)");

            for(var i = 0; i < $itemsToHide.length; i++) {
                $itemsToHide[i].setAttribute(displayAttr, hiddenMode);
            }

             try {
                $foundItems = $target.querySelectorAll(matchingSelector);

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
                            $parent.setAttribute(displayAttr, visibleMode);
                        }

                        if($previousElement && $previousElement.tagName === "H2") {
                            $previousElement.setAttribute(displayAttr, visibleMode);
                        }
                    }
                }
            } catch (e) {}

            for(var j = 0; j < $sections.length; j++) {
                var $section = $sections[j];

                if($section.querySelector("table[" + displayAttr + "='true']")) {
                    $section.querySelector("h1").setAttribute(displayAttr, visibleMode);
                }
            }

            window.Tracy.Debug.panels['tracy-debug-panel-PhpInfoPanel'].reposition();
        }
    }
});