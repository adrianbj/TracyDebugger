function tracyResizePanel(panel, type) {
    panel = document.getElementById("tracy-debug-panel-" + panel);
    panel.style.left = '0px';
    panel.style.width = 'calc(100vw - 15px)';
    if(type == 'fullscreen') {
        panel.style.top = '0px';
        panel.style.height = 'calc(100vh - 22px)';
    }
    else {
        panel.style.top = '50vh';
        panel.style.height = 'calc(50vh - 22px)';
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
