function openAdminer(queryStr) {
    if(!window.AdminerRendererUrl) return;
    var url = window.AdminerRendererUrl + "?" + queryStr;
    var panelEl = document.getElementById("tracy-debug-panel-ProcessWire-AdminerPanel");
    if(!panelEl || !window.Tracy || !window.Tracy.Debug || !window.Tracy.Debug.panels) {
        window.requestAnimationFrame(function() { openAdminer(queryStr); });
    }
    else if(panelEl.classList.contains("tracy-mode-window")) {
        document.getElementById('adminer-iframe').src = url;
    }
    else {
        var panel = window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-AdminerPanel"];
        if(panel.elem.dataset.tracyContent) {
            panel.init();
        }
        document.getElementById('adminer-iframe').src = url;
        panel.toFloat();
        panel.focus();
    }
}

(function initAdminerHandler() {
    function setupAdminerClickHandler() {
        document.body.addEventListener("click", function(e) {
            if (e.target) {
                let curEl = e.target;
                while (curEl && curEl.tagName !== "A") {
                    curEl = curEl.parentNode;
                }

                if (curEl && curEl.href && curEl.href.indexOf("adminer://") !== -1) {
                    e.preventDefault();
                    const queryStr = curEl.href.split('?')[1];
                    if (e.shiftKey) {
                        window.location = curEl.href.replace("adminer://", window.AdminerUrl);
                    } else {
                        openAdminer(queryStr);
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupAdminerClickHandler);
    } else {
        setupAdminerClickHandler();
    }
})();
