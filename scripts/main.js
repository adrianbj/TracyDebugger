function tracyResizePanel(panel) {
    panel = document.getElementById("tracy-debug-panel-ProcessWire-" + panel);
    var tracyPanel = window.Tracy.Debug.panels[panel.id];
    tracyPanel.elem.classList.add('tracy-panel-resized');
    tracyPanel.elem.dataset.tracyContent = true; // hack to satisy condition in Tracy's restorePosition() method

    let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
    let vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)

    var maxPanelWidth = (window.clientHeight < window.scrollHeight ? (vw - 15) : vw) + 'px';
    var maxPanelHeight = (vh - 44) + 'px';

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
    localStorage.setItem("remove-tracy-debug-panel-ProcessWire-" + panel + "Panel", 1);
}

// Delegated listeners for data-attribute-driven actions (replaces inline handlers)
document.addEventListener('click', function(e) {
    var el = e.target.closest('[data-tracy-resize]');
    if(el) {
        e.preventDefault();
        tracyResizePanel(el.getAttribute('data-tracy-resize'));
        return;
    }
    el = e.target.closest('[data-tracy-close]');
    if(el) {
        tracyClosePanel(el.getAttribute('data-tracy-close'));
        return;
    }
    el = e.target.closest('[data-tracy-filterbox-clear]');
    if(el) {
        var input = el.parentElement.querySelector('input');
        input.getFilterBox().clearFilterBox();
        input.focus();
        return;
    }
    el = e.target.closest('[data-dumps-toggle]');
    if(el) {
        tracyDumpsToggler(el, el.getAttribute('data-dumps-toggle') === 'expand');
        return;
    }
    el = e.target.closest('[data-dump-type]');
    if(el) {
        e.preventDefault();
        toggleDumpType(el, el.getAttribute('data-dump-type'), el.getAttribute('data-dump-class-ext'));
        return;
    }
    el = e.target.closest('[data-raw-file-editor-code]');
    if(el && typeof tracyFileEditor !== 'undefined') {
        tracyFileEditor.getRawFileEditorCode();
        return;
    }
}, true);

document.addEventListener('submit', function(e) {
    if(!e.target.closest || !e.target.closest('.tracy-panel')) return;
    var confirm_msg = e.target.getAttribute('data-confirm');
    if(confirm_msg) {
        if(!confirm(confirm_msg)) {
            e.preventDefault();
        }
    }
});

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

(function() {
    if(window.__tracyMdCopyHandlerInstalled) return;
    window.__tracyMdCopyHandlerInstalled = true;

    function tracyMdDoCopy(btn, md) {
        var origChildren = [];
        for(var i = 0; i < btn.childNodes.length; i++) origChildren.push(btn.childNodes[i]);
        var done = function(ok) {
            while(btn.firstChild) btn.removeChild(btn.firstChild);
            var svgNS = 'http://www.w3.org/2000/svg';
            var icon = document.createElementNS(svgNS, 'svg');
            icon.setAttribute('width', '14');
            icon.setAttribute('height', '14');
            icon.setAttribute('viewBox', '0 0 24 24');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', ok ? '#4CAF50' : '#CD1818');
            icon.setAttribute('stroke-width', '3');
            icon.setAttribute('stroke-linecap', 'round');
            icon.setAttribute('stroke-linejoin', 'round');
            var p = document.createElementNS(svgNS, 'path');
            p.setAttribute('d', ok ? 'M5 13 L10 18 L19 6' : 'M6 6 L18 18 M6 18 L18 6');
            icon.appendChild(p);
            btn.appendChild(icon);
            setTimeout(function() {
                while(btn.firstChild) btn.removeChild(btn.firstChild);
                for(var j = 0; j < origChildren.length; j++) btn.appendChild(origChildren[j]);
            }, 1200);
        };
        if(navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
            navigator.clipboard.writeText(md).then(function() { done(true); }, function(err) { console.error('tracy md-copy: clipboard write rejected', err); done(false); });
        } else {
            var ta = document.createElement('textarea');
            ta.value = md;
            ta.style.cssText = 'position:fixed;opacity:0';
            document.body.appendChild(ta);
            ta.select();
            try { done(document.execCommand('copy')); } catch(err) { console.error('tracy md-copy: execCommand failed', err); done(false); }
            document.body.removeChild(ta);
        }
    }

    function tracyIsInlineSource(s) {
        return !s.closest('#tracy-debug') && !s.closest('.tracy-panel');
    }

    function tracyInlineSources() {
        var all = document.querySelectorAll('[data-tracy-md-source]');
        var out = [];
        for(var i = 0; i < all.length; i++) {
            if(tracyIsInlineSource(all[i])) out.push(all[i]);
        }
        return out;
    }

    function tracyScopeSources(btn) {
        if(btn.hasAttribute('data-tracy-page-dumps')) return tracyInlineSources();
        var panel = btn.closest('.tracy-panel');
        var root = panel || document;
        return Array.prototype.slice.call(root.querySelectorAll('[data-tracy-md-source]'));
    }

    function tracyDumpTitle(source) {
        var panel = source.closest('.tracy-DumpPanel');
        if(panel) {
            var h2 = panel.querySelector(':scope > h2');
            if(h2) return h2.textContent.trim();
        }
        var entries = source.closest('#tracyDumpEntries');
        if(entries) {
            var node = source;
            while(node && node.parentElement !== entries) node = node.parentElement;
            if(node) {
                var prev = node.previousElementSibling;
                if(prev && prev.tagName === 'H2') return prev.textContent.trim();
            }
        }
        return '';
    }

    function tracyBuildMarkdown(sources) {
        var parts = [];
        for(var i = 0; i < sources.length; i++) {
            var text;
            try { text = JSON.parse(sources[i].textContent); } catch(err) { continue; }
            if(text == null || text === '') continue;
            var title = tracyDumpTitle(sources[i]);
            parts.push(title ? '## ' + title + '\n\n' + text : text);
        }
        return parts.join('\n\n');
    }

    document.addEventListener('click', function(e) {
        var allBtn = e.target.closest && e.target.closest('[data-tracy-md-copy-all]');
        if(allBtn) {
            e.preventDefault();
            e.stopPropagation();
            var md = tracyBuildMarkdown(tracyScopeSources(allBtn));
            if(md === '') return;
            tracyMdDoCopy(allBtn, md);
            return;
        }
        var btn = e.target.closest && e.target.closest('[data-tracy-md-copy]');
        if(!btn) return;
        e.preventDefault();
        e.stopPropagation();
        var src = btn.parentElement && btn.parentElement.querySelector('[data-tracy-md-source]');
        if(!src) return;
        var md2;
        try { md2 = JSON.parse(src.textContent); } catch(err) { console.error('tracy md-copy: JSON parse failed', err); return; }
        tracyMdDoCopy(btn, md2);
    }, true);

    function tracyCopyAllBar(extraClass, pageScoped) {
        var icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>';
        var bar = document.createElement('div');
        bar.className = 'tracy-md-copy-all-bar' + (extraClass ? ' ' + extraClass : '');
        bar.innerHTML = '<button type="button" data-tracy-md-copy-all' + (pageScoped ? ' data-tracy-page-dumps' : '') + ' class="tracy-md-copy-all-btn" title="Copy all dumps as plaintext for an AI agent">' + icon + '<span>Copy all</span></button>';
        return bar;
    }

    function tracyInstallPageCopyAll() {
        var inline = tracyInlineSources();
        if(inline.length < 2) return;
        if(document.querySelector('[data-tracy-md-copy-all][data-tracy-page-dumps]')) return;
        var last = inline[inline.length - 1];
        var block = last.closest('.tracy-inner') || last.closest('.tracy-DumpPanel') || last;
        if(!block || !block.parentNode) return;
        block.parentNode.insertBefore(tracyCopyAllBar('tracy-md-copy-all-page', true), block.nextSibling);
    }

    function tracyInstallConsoleCopyAll() {
        var rd = document.getElementById('tracyConsoleResult');
        if(!rd) return;
        function ensure() {
            var existing = rd.querySelector('.tracy-md-copy-all-console');
            var count = rd.querySelectorAll('[data-tracy-md-source]').length;
            if(count >= 2) {
                if(existing) rd.appendChild(existing);
                else rd.appendChild(tracyCopyAllBar('tracy-md-copy-all-console', false));
            } else if(existing) {
                existing.parentNode.removeChild(existing);
            }
        }
        var obs = new MutationObserver(function() {
            obs.disconnect();
            ensure();
            obs.observe(rd, { childList: true });
        });
        obs.observe(rd, { childList: true });
        ensure();
    }

    function tracyInstallCopyAll() {
        tracyInstallPageCopyAll();
        tracyInstallConsoleCopyAll();
    }

    if(document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tracyInstallCopyAll);
    } else {
        tracyInstallCopyAll();
    }
})();

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
	            "data-tracy-filterbox-clear": "1"
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
