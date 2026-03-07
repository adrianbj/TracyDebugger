if(!tracyExceptionLoader) {
    var tracyExceptionLoader = {

        getFileLineVars: function (query,variable) {
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
        },

        getCookie: function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        },

        populateExceptionViewer: function(filePath) {

            if(typeof tracyExceptionsViewer === "undefined") {
                window.requestAnimationFrame(tracyExceptionLoader.populateExceptionViewer(filePath));
            }
            else {

                document.getElementById("tracyExceptionFilePath").value = filePath;
                document.cookie = "tracyExceptionFile=" + filePath + "; path=/; SameSite=Strict";
                document.getElementById("panelTitleFilePath").innerHTML = filePath.replace('site/assets/logs/tracy/', '');

                var xmlhttp;
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                        if(xmlhttp.status == 200 && xmlhttp.responseText !== "" && xmlhttp.responseText !== "[]") {
                            var fileData = JSON.parse(xmlhttp.responseText);
                            var viewerCode = document.getElementById("tracyExceptionsViewerCode");
                            var isBlueScreen = fileData.contents.replace(/^\s+/, '').substring(0, 15).toLowerCase() === '<!doctype html>';

                            if(isBlueScreen) {
                                if(document.getElementById('tracy-bs')) {
                                    document.getElementById('tracy-bs').remove();
                                }
                                viewerCode.innerHTML = fileData.contents;
                                var tracyBs = document.getElementById("tracy-bs");
                                if(tracyBs) tracyBs.style.zIndex = "100";
                            } else {
                                // Hide old BlueScreen instead of removing to avoid ResizeObserver stickyFooter crash
                                var oldBs = document.getElementById('tracy-bs');
                                if(oldBs) oldBs.style.display = 'none';
                                viewerCode.innerHTML = "";
                                viewerCode.style.display = "";
                                var pre = document.createElement("pre");
                                pre.style.cssText = "padding:10px; white-space:pre-wrap; word-wrap:break-word; font-size:13px; margin:0;";
                                pre.textContent = fileData.contents;
                                viewerCode.appendChild(pre);
                            }
                        }
                        xmlhttp.getAllResponseHeaders();
                    }
                };
                xmlhttp.open("POST", tracyExceptionsViewer.currentURL, true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("filePath=" + filePath + "&csrfToken=" + encodeURIComponent(tracyExceptionsViewer.csrfToken));
            }
        },

        loadExceptionFile: function(filePath) {
            if(document.getElementById("tracy-debug-panel-ProcessWire-TracyExceptionsPanel").classList.contains("tracy-mode-window")) {
                this.populateExceptionViewer(filePath);
            }
            else {
                if(!window.Tracy.Debug.panels || !document.getElementById("tracy-debug-panel-ProcessWire-TracyExceptionsPanel")) {
                    window.requestAnimationFrame(tracyExceptionLoader.loadExceptionFile(filePath));
                }
                else {
                    var panel = window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-TracyExceptionsPanel"];
                    if(panel.elem.dataset.tracyContent) {
                        panel.init();
                    }
                    this.populateExceptionViewer(filePath);
                    panel.toFloat();
                    panel.focus();
                }
            }
        }
    };

    (function initTracyExceptionHandler() {
        function setupTracyClickHandler() {
            const htmlElement = document.documentElement;
            const doc = htmlElement.classList.contains('tracy-bs-visible') ? document.body : document;

            doc.addEventListener("click", function(e) {
                if (e.target) {
                    let curEl = e.target;

                    while (curEl && curEl.tagName !== "A") {
                        curEl = curEl.parentNode;
                    }

                    if (curEl && curEl.href && curEl.href.indexOf("tracyexception://") !== -1) {
                        e.preventDefault();
                        const queryStr = curEl.href.split('?')[1];
                        const fullFilePath = tracyExceptionLoader.getFileLineVars(queryStr, "f");
                        tracyExceptionLoader.loadExceptionFile(fullFilePath);
                    }

                    const tracyExceptionFiles = document.getElementById("tracyExceptionFiles");
                    if (tracyExceptionFiles) {
                        const tracyExceptions = tracyExceptionFiles.getElementsByTagName("li");
                        const length = tracyExceptions.length;
                        for (let i = 0; i < length; i++) {
                            const queryStr = tracyExceptions[i].getElementsByTagName("a")[0].href.split('?')[1];
                            const currentFilePath = decodeURI(queryStr.replace('f=', '').replace('&l=1', '')).replace('site/assets/logs/tracy/', '');
                            tracyExceptions[i].getElementsByTagName("a")[0].className =
                                document.getElementById('panelTitleFilePath').innerHTML === currentFilePath ? "active" : "";
                        }
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupTracyClickHandler);
        } else {
            // DOM already loaded
            setupTracyClickHandler();
        }
    })();

}
