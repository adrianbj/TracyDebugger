function tracyResizePanel(panel) {
    panel = document.getElementById("tracy-debug-panel-" + panel);
    var tracyPanel = window.Tracy.Debug.panels[panel.id];
    tracyPanel.elem.classList.add('tracy-panel-resized');
    tracyPanel.elem.dataset.tracyContent = true; // hack to satisy condition in Tracy's restorePosition() method

    var maxPanelWidth = window.clientHeight < window.scrollHeight ? 'calc(100vw - 15px)' : '100vw';
    var maxPanelHeight = 'calc(100vh - 44px)';

    if(panel.style.width == maxPanelWidth && panel.style.height == maxPanelHeight) {
        tracyPanel.restorePosition();
    }
    else {
        tracyPanel.savePosition();
        panel.style.left = '0px';
        panel.style.width = maxPanelWidth;
        panel.style.top = '0px';
        panel.style.height = maxPanelHeight;
    }
}

function tracyClosePanel(panel) {
    localStorage.setItem("remove-tracy-debug-panel-" + panel + "Panel", 1);
}

// panel rollup thanks to @tpr / @rolandtoth
// jQuery .on() equivalent
var filterEventHandler = function (selector, callback) {
    return (!callback || !callback.call) ? null : function (e) {
        var target = e.target || e.srcElement || null;
        while (target && target.parentElement && target.parentElement.querySelectorAll) {
            var elms = target.parentElement.querySelectorAll(selector);
            for (var i = 0; i < elms.length; i++) {
                if (elms[i] === target) {
                    e.filterdTarget = elms[i];
                    callback.call(elms[i], e);
                    return;
                }
            }
            target = target.parentElement;
        }
    };
};

// toggle rollup state on double click
document.addEventListener("dblclick", filterEventHandler(".tracy-panel h1", function (e) {
    e.filterdTarget.parentElement.classList.toggle("tracy-mode-rollup");
}));

// remove rolled up state on closing the panel
document.addEventListener("mouseup", filterEventHandler(".tracy-panel.tracy-mode-rollup [rel=\'close\']", function (e) {
    e.filterdTarget.parentElement.parentElement.classList.remove("tracy-mode-rollup");
}));

// hide rolled up panels to allow Tracy to save their position correctly
window.addEventListener("beforeunload", function () {
    var $rollupPanels = document.querySelectorAll(".tracy-mode-rollup");

    if($rollupPanels.length) {
        for(var i = 0; i < $rollupPanels.length; i++) {
            $rollupPanels[i].style.visibility = "hidden";
            $rollupPanels[i].classList.remove("tracy-mode-rollup");
        }
    }
 });

var toggleDumpType = function(el, type, classExt) {
    var dumpTabs = document.getElementsByClassName('tracyDumpTabs_' + classExt);
    [].forEach.call(dumpTabs, function (tab) {
        var tabContentEl = document.getElementById(tab.id);
        var toggleableParent = tabContentEl.querySelector('.tracy-toggle');
        if(tab.id.indexOf(type) === -1) {
            if(toggleableParent) window.Tracy.Toggle.toggle(toggleableParent, false);
            document.getElementById(tab.id.replace('_', 'Tab_')).classList.remove('active');
            tabContentEl.style.display = "none";
        }
        else {
            if(toggleableParent) window.Tracy.Toggle.toggle(toggleableParent);
            document.getElementById(tab.id.replace('_', 'Tab_')).classList.add('active');
            tabContentEl.style.display = "block";
        }
    });
    window.Tracy.Debug.panels[el.closest('.tracy-panel').id].reposition();
}

function tracyDumpsToggler(el, show) {
    var i=0;
    [].forEach.call(el.parentElement.querySelectorAll('.tracy-toggle'), function(item) {
        if(i>0 || i==0 && item.classList.contains('tracy-collapsed')) window.Tracy.Toggle.toggle(item, show);
        i++;
    });
};

// reposition panel if comment opened (for Captain Hook and API Explorer panels)
document.addEventListener("click", filterEventHandler(".comment", function (e) {
    setTimeout(function() {
        window.Tracy.Debug.panels[e.filterdTarget.closest('.tracy-panel').id].reposition();
    }, 250);
}));

// focus filterbox input for currently focused panel
document.addEventListener("keydown", function(e) {
    if(e.altKey && (e.keyCode==70||e.charCode==70)) {
        e.preventDefault();
        var focusedPanel = document.querySelector(".tracy-focused");
        if(focusedPanel) {
            focusedPanel.querySelector("[id$='FilterBoxWrap']").querySelector("input").focus();
        }
    }
});

/*!
* Setup general FilterBox panel displays.

* @param  {FilterBox}   fbx   FilterBox instance
* @return {object}   object of displays
*/
function setupTracyPanelFilterBoxDisplays (fbx) {
	var wrapperId = "#" + fbx.getInput().parentElement.id;

	return {
        counter: {
            tag: "p",
            addTo: {
                selector: wrapperId,
                position: "append"
            },
            attrs: {
                class: "tracy-filterbox-counter"
            },
            text: function () {
	            var text = "";

	            if(fbx.getFilter() !== "") {
		            var matches = fbx.countVisible(),
		            	total = fbx.countTotal();

	                text = matches ? "<span>" + matches + "</span>/" + total : "No match";
	            }

	            return text;
            }
        },
        clearButton: {
	        tag: "span",
	        addTo: {
	            selector: wrapperId,
	            position: "append"
	        },
	        attrs: {
	            class: "tracy-filterbox-clear",
	            onclick: "var input = this.parentElement.querySelector('input'); input.getFilterBox().clearFilterBox(); input.focus();"
	        },
	        text: function () {
	            return fbx.getFilter() ? "&times;" : "";
	        }
	    }
    };
}

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