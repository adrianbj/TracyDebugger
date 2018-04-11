function tracyResizePanel(panel, type) {
    panel = document.getElementById("tracy-debug-panel-"+panel);
    panel.style.left = '0px';
    if(type == 'fullscreen') {
        panel.style.top = '0px';
        panel.style.height = '100vh';
        panel.style.width = '100vw';
    }
    else {
        panel.style.top = 'calc(50vh - 22px)';
        panel.style.height = '50vh';
        panel.style.width = '100vw';
    }
}