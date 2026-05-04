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
                window.requestAnimationFrame(function() { tracyExceptionLoader.populateExceptionViewer(filePath); });
            }
            else {

                document.getElementById("tracyExceptionFilePath").value = filePath;
                document.cookie = "tracyExceptionFile=" + filePath + "; path=/; SameSite=Strict";
                document.getElementById("panelTitleFilePath").textContent = filePath.replace('site/assets/logs/tracy/', '');

                var xmlhttp;
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                        if(xmlhttp.status == 200 && xmlhttp.responseText !== "" && xmlhttp.responseText !== "[]") {
                            var fileData = JSON.parse(xmlhttp.responseText);
                            var viewerCode = document.getElementById("tracyExceptionsViewerCode");
                            var isBlueScreen = fileData.contents.replace(/^\s+/, '').substring(0, 15).toLowerCase() === '<!doctype html>';

                            if(isBlueScreen) {
                                var parser = new DOMParser();
                                var doc = parser.parseFromString(fileData.contents, "text/html");
                                var styles = doc.querySelectorAll("style");
                                var bsEl = doc.getElementById("tracy-bs");
                                var bsContent = "";
                                styles.forEach(function(s) { bsContent += s.outerHTML; });
                                if(bsEl) bsContent += bsEl.outerHTML;

                                if(typeof tracyFileEditorLoader !== "undefined" && bsEl) {
                                    var tempDiv = document.createElement("div");
                                    tempDiv.innerHTML = bsContent;
                                    tempDiv.querySelectorAll("a.tracy-editor").forEach(function(a) {
                                        var href = a.getAttribute("href");
                                        if(!href || href.indexOf("tracy://") === 0) return;
                                        var file, line;
                                        var qMatch = href.match(/[?&]f(?:ile)?=([^&]+)/);
                                        if(qMatch) {
                                            file = qMatch[1];
                                            var lMatch = href.match(/[?&]l(?:ine)?=([^&]+)/);
                                            line = lMatch ? lMatch[1] : 1;
                                        } else {
                                            var stripped = href.replace(/^[a-z]+:\/\/[^/]*\/?/, "");
                                            var colonIdx = stripped.lastIndexOf(":");
                                            if(colonIdx > 0 && /^\d+$/.test(stripped.substring(colonIdx + 1))) {
                                                file = stripped.substring(0, colonIdx);
                                                line = stripped.substring(colonIdx + 1);
                                            } else {
                                                file = stripped;
                                                line = 1;
                                            }
                                        }
                                        if(file) {
                                            a.setAttribute("href", "tracy://?f=" + file + "&l=" + line);
                                        }
                                    });
                                    bsContent = tempDiv.innerHTML;
                                }

                                Tracy.BlueScreen.loadAjax(bsContent);
                            }
                            else {
                                // Non-HTML content — display using Tracy's built-in
                                // BlueScreen viewer which handles z-index, toggle,
                                // ESC dismiss, and scroll management automatically
                                var tempEl = document.createElement("div");
                                tempEl.textContent = fileData.contents;
                                var escapedContents = tempEl.innerHTML;

                                var bsContent =
                                    '<style>' +
                                    '#tracy-bs{font:9pt/1.5 Verdana,sans-serif;background:white;color:#333;position:absolute;left:0;top:0;width:100%;text-align:left}' +
                                    '#tracy-bs-toggle{position:absolute;right:.5em;top:.5em;text-decoration:none;background:#CD1818;color:white!important;padding:3px}' +
                                    '#tracy-bs-toggle.tracy-collapsed{position:fixed}' +
                                    '.tracy-bs-main{display:flex;flex-direction:column;padding-bottom:80vh}' +
                                    '.tracy-bs-main.tracy-collapsed{display:none}' +
                                    '#tracy-bs .tracy-section{padding:20px}' +
                                    '#tracy-bs .tracy-section--error{background:#CD1818;color:white}' +
                                    '#tracy-bs .tracy-section--error h1{font-size:15pt;font-weight:normal;text-shadow:1px 1px 2px rgba(0,0,0,.3);color:white;margin:0}' +
                                    '#tracy-bs pre{font:9pt/1.5 Consolas,monospace!important;background:#FDF5CE;padding:.4em .7em;border:2px solid #ffffffa6;overflow:auto;white-space:pre-wrap;word-wrap:break-word}' +
                                    '#tracy-bs footer ul{font-size:7pt;padding:20px;margin:0;color:#777;background:#F6F5F3;border-top:1px solid #DDD;list-style:none}' +
                                    '#tracy-bs .tracy-footer--sticky{position:fixed;width:100%;bottom:0}' +
                                    '</style>' +
                                    '<tracy-div id="tracy-bs" itemscope>' +
                                    '<a id="tracy-bs-toggle" href="#" class="tracy-toggle">&#xfeff;</a>' +
                                    '<div class="tracy-bs-main">' +
                                    '<section class="tracy-section tracy-section--error">' +
                                    '<h1><span></span></h1>' +
                                    '</section>' +
                                    '<section class="tracy-section">' +
                                    '<pre>' + escapedContents + '</pre>' +
                                    '</section>' +
                                    '<footer><ul><li>Exception log file</li></ul></footer>' +
                                    '</div>' +
                                    '</tracy-div>';

                                Tracy.BlueScreen.loadAjax(bsContent);
                            }

                            var tracyBs = document.getElementById("tracy-bs");
                            if(tracyBs) tracyBs.style.zIndex = "100";

                        }
                        xmlhttp.getAllResponseHeaders();
                    }
                };
                xmlhttp.open("POST", tracyExceptionsViewer.currentURL, true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("filePath=" + encodeURIComponent(filePath) + "&csrfToken=" + encodeURIComponent(tracyExceptionsViewer.csrfToken));
            }
        },

        copyMarkdown: function(btn) {
            var mdPath = btn.getAttribute("data-md-path");
            if(!mdPath) return;

            var originalText = btn.textContent;
            var originalBg = btn.style.background;
            var resetButton = function(text, bg) {
                btn.textContent = text;
                btn.style.background = bg;
                setTimeout(function() {
                    btn.textContent = originalText;
                    btn.style.background = originalBg;
                }, 1200);
            };

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if(xhr.readyState !== XMLHttpRequest.DONE) return;
                if(xhr.status === 200 && xhr.responseText) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        var contents = data && typeof data.contents === "string" ? data.contents : "";
                        if(navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(contents).then(function() {
                                resetButton("✓", "#4CAF50");
                            }).catch(function() {
                                resetButton("✗", "#CD1818");
                            });
                        }
                        else {
                            var ta = document.createElement("textarea");
                            ta.value = contents;
                            ta.style.position = "fixed";
                            ta.style.opacity = "0";
                            document.body.appendChild(ta);
                            ta.select();
                            try {
                                document.execCommand("copy");
                                resetButton("✓", "#4CAF50");
                            } catch(err) {
                                resetButton("✗", "#CD1818");
                            }
                            document.body.removeChild(ta);
                        }
                    } catch(err) {
                        resetButton("✗", "#CD1818");
                    }
                }
                else {
                    resetButton("✗", "#CD1818");
                }
            };
            xhr.open("POST", tracyExceptionsViewer.currentURL, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.send("filePath=" + encodeURIComponent(mdPath) + "&csrfToken=" + encodeURIComponent(tracyExceptionsViewer.csrfToken));
        },

        loadExceptionFile: function(filePath) {
            if(document.getElementById("tracy-debug-panel-ProcessWire-TracyExceptionsPanel").classList.contains("tracy-mode-window")) {
                this.populateExceptionViewer(filePath);
            }
            else {
                if(!window.Tracy.Debug.panels || !document.getElementById("tracy-debug-panel-ProcessWire-TracyExceptionsPanel")) {
                    window.requestAnimationFrame(function() { tracyExceptionLoader.loadExceptionFile(filePath); });
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
                    if (e.target.classList && e.target.classList.contains("tracy-md-copy-btn")) {
                        e.preventDefault();
                        e.stopPropagation();
                        tracyExceptionLoader.copyMarkdown(e.target);
                        return;
                    }

                    let curEl = e.target;

                    while (curEl && curEl.tagName !== "A") {
                        curEl = curEl.parentNode;
                    }

                    if (curEl && curEl.href && curEl.href.indexOf("tracyexception://") !== -1) {
                        e.preventDefault();
                        const queryStr = curEl.href.split('?')[1];
                        const fullFilePath = tracyExceptionLoader.getFileLineVars(queryStr, "f");
                        tracyExceptionLoader.loadExceptionFile(fullFilePath);
                        if (typeof showUnloadButton === 'function') showUnloadButton();
                    }

                    const tracyExceptionFiles = document.getElementById("tracyExceptionFiles");
                    if (tracyExceptionFiles) {
                        const tracyExceptions = tracyExceptionFiles.getElementsByTagName("li");
                        const length = tracyExceptions.length;
                        for (let i = 0; i < length; i++) {
                            const queryStr = tracyExceptions[i].getElementsByTagName("a")[0].href.split('?')[1];
                            const currentFilePath = decodeURI(queryStr.replace('f=', '').replace('&l=1', '')).replace('site/assets/logs/tracy/', '');
                            tracyExceptions[i].getElementsByTagName("a")[0].className =
                                document.getElementById('panelTitleFilePath').textContent === currentFilePath ? "active" : "";
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
