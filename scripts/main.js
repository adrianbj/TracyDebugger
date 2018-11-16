function tracyResizePanel(panel) {
    panel = document.getElementById("tracy-debug-panel-" + panel);
    tracyPanel = window.Tracy.Debug.panels[panel.id];
    tracyPanel.elem.classList.add('tracy-panel-resized');
    tracyPanel.elem.dataset.tracyContent = true; // hack to satisy condition in Tracy's restorePosition() method
    var maximizedPanelSettings = JSON.parse(localStorage.getItem(panel.id + '-maximizedSettings'));
    if(maximizedPanelSettings) {
        localStorage.setItem(panel.id, localStorage.getItem(panel.id + '-maximizedSettings'));
        tracyPanel.restorePosition();
        localStorage.removeItem(panel.id + '-maximizedSettings');
    }
    else {
        tracyPanel.savePosition();
        localStorage.setItem(panel.id + '-maximizedSettings', JSON.stringify(JSON.parse(localStorage.getItem(panel.id))));
        panel.style.left = '0px';
        panel.style.width = window.clientHeight < window.scrollHeight ? 'calc(100vw - 15px)' : '100vw';
        panel.style.top = '0px';
        panel.style.height = 'calc(100vh - 44px)';
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
        if(i>0 || i==0 && item.classList.contains('tracy-collapsed')) tdToggle(item, show);
        i++;
    });
    window.Tracy.Debug.panels[el.closest('.tracy-panel').id].reposition();
};

// this is a copy of the Tracy core toggle() function but without the creation of the tracy-toggle event to make it faster
// changes element visibility
var  tdToggle = function(el, show) {
    var collapsed = el.classList.contains('tracy-collapsed'),
        ref = el.getAttribute('data-tracy-ref') || el.getAttribute('href', 2),
        dest = el;

    if (typeof show === 'undefined') {
        show = collapsed;
    } else if (!show === collapsed) {
        return;
    }

    if (!ref || ref === '#') {
        ref = '+';
    } else if (ref.substr(0, 1) === '#') {
        dest = document;
    }
    ref = ref.match(/(\^\s*([^+\s]*)\s*)?(\+\s*(\S*)\s*)?(.*)/);
    dest = ref[1] ? dest.parentNode : dest;
    dest = ref[2] ? dest.closest(ref[2]) : dest;
    dest = ref[3] ? window.Tracy.Toggle.nextElement(dest.nextElementSibling, ref[4]) : dest;
    dest = ref[5] ? dest.querySelector(ref[5]) : dest;

    el.classList.toggle('tracy-collapsed', !show);
    dest.classList.toggle('tracy-collapsed', !show);
}

// reposition panel if comment opened (for Captain Hook and API Explorer panels)
document.addEventListener("click", filterEventHandler(".comment", function (e) {
    setTimeout(function(){
        window.Tracy.Debug.panels[e.filterdTarget.closest('.tracy-panel').id].reposition();
    }, 250);
}));