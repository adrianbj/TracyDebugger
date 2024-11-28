function openAdminer(queryStr) {
    var url = window.AdminerRendererUrl + "?" + queryStr;
    if(document.getElementById("tracy-debug-panel-AdminerPanel").classList.contains("tracy-mode-window")) {
        document.getElementById('adminer-iframe').src = url;
    }
    else {
        if(!window.Tracy.Debug.panels || !document.getElementById("tracy-debug-panel-AdminerPanel")) {
            window.requestAnimationFrame(openAdminer(url));
        }
        else {
            var panel = window.Tracy.Debug.panels["tracy-debug-panel-AdminerPanel"];
            if(panel.elem.dataset.tracyContent) {
                panel.init();
            }
            document.getElementById('adminer-iframe').src = url;
            panel.toFloat();
            panel.focus();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener("click", function(e) {
        if(e.target) {
            var curEl = e.target;
            while(curEl && curEl.tagName != "A") {
                curEl = curEl.parentNode;
            }
            if(curEl && curEl.href && curEl.href.indexOf("adminer://") !== -1) {
                e.preventDefault();
                var queryStr = curEl.href.split('?')[1];
                if (e.shiftKey) {
                    window.location = curEl.href.replace("adminer://", window.AdminerUrl);
                } else {
                    openAdminer(queryStr);
                }
            }
        }
    });
});
