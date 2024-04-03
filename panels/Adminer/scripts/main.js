document.documentElement.className += (window.self == window.top ? " top" : " framed");

const queryStr = new URLSearchParams(window.location.search).toString();
window.parent.postMessage(queryStr, "*");
