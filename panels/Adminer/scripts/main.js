document.documentElement.className += (window.self == window.top ? " top" : " framed");

let updateWindowDebounceTimer = 0;
function updateWindow() {
    clearTimeout(updateWindowDebounceTimer);
    updateWindowDebounceTimer = setTimeout(sendUpdate, 100);
}
function sendUpdate() {
    updateWindowDebounceTimer = 0;
    const queryStr = new URLSearchParams(window.location.search).toString();
    window.parent.postMessage(queryStr, window.HttpRootUrl);
}
updateWindow();
