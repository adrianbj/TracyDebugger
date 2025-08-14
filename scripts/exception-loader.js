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
                document.cookie = "tracyExceptionFile=" + filePath + "; path=/";
                document.getElementById("panelTitleFilePath").innerHTML = filePath.replace('site/assets/logs/tracy/', '');

                var xmlhttp;
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                        if(xmlhttp.status == 200 && xmlhttp.responseText !== "" && xmlhttp.responseText !== "[]") {
                            var fileData = JSON.parse(xmlhttp.responseText);
                            if(document.getElementById('tracy-bs')) {
                                document.getElementById('tracy-bs').remove();
                            }
                            document.getElementById("tracyExceptionsViewerCode").innerHTML = fileData.contents;
                            document.getElementById("tracy-bs").style.zIndex = "100";
                        }
                        xmlhttp.getAllResponseHeaders();
                    }
                };
                xmlhttp.open("POST", tracyExceptionsViewer.currentUrl, true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("filePath=" + filePath);
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
