if(!tracyExceptionLoader) {
    var tracyExceptionLoader = {

        PANEL_ID_PREFIX: "tracy-debug-panel-ProcessWire-TracyExceptionsPanel",

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

        findOriginatingPanel: function(el) {
            while (el && el.nodeType === 1) {
                if (el.id && el.id.indexOf(tracyExceptionLoader.PANEL_ID_PREFIX) === 0) {
                    return el;
                }
                el = el.parentNode;
            }
            return null;
        },

        getMainPanel: function() {
            return document.getElementById(tracyExceptionLoader.PANEL_ID_PREFIX);
        },

        updateFileTreeHighlight: function(panelEl) {
            var fileTree = panelEl.querySelector("#tracyExceptionFiles");
            if(!fileTree) return;
            var title = panelEl.querySelector("#panelTitleFilePath");
            var currentFile = title ? title.textContent : "";
            var lis = fileTree.getElementsByTagName("li");
            for(var i = 0; i < lis.length; i++) {
                var anchor = lis[i].getElementsByTagName("a")[0];
                if(!anchor || !anchor.href) continue;
                var queryStr = anchor.href.split('?')[1];
                if(!queryStr) continue;
                var thisFile = decodeURI(queryStr.replace('f=', '').replace('&l=1', '')).replace('site/assets/logs/tracy/', '');
                anchor.className = currentFile === thisFile ? "active" : "";
            }
        },

        clearViewer: function(panelEl) {
            if(!panelEl) return;
            var btn = panelEl.querySelector("#clearException");
            if(btn) btn.style = 'display: none !important';
            var title = panelEl.querySelector("#panelTitleFilePath");
            if(title) title.innerHTML = '';
            var bs = document.getElementById('tracy-bs');
            if(bs) {
                while(bs.firstChild) bs.removeChild(bs.firstChild);
                bs.appendChild(document.createElement("footer"));
            }
            tracyExceptionLoader.updateFileTreeHighlight(panelEl);
        },

        showUnloadButton: function(panelEl) {
            if(!panelEl) return;
            var btn = panelEl.querySelector("#clearException");
            if(btn) btn.style.display = "inline-block";
        },

        populateExceptionViewer: function(filePath, panelEl) {

            if(!panelEl) panelEl = tracyExceptionLoader.getMainPanel();
            if(!panelEl) return;

            if(typeof tracyExceptionsViewer === "undefined") {
                window.requestAnimationFrame(function() { tracyExceptionLoader.populateExceptionViewer(filePath, panelEl); });
            }
            else {

                var filePathInput = panelEl.querySelector("#tracyExceptionFilePath");
                if(filePathInput) filePathInput.value = filePath;
                document.cookie = "tracyExceptionFile=" + filePath + "; path=/; SameSite=Strict";
                var titleEl = panelEl.querySelector("#panelTitleFilePath");
                if(titleEl) titleEl.textContent = filePath.replace('site/assets/logs/tracy/', '');

                tracyExceptionLoader.updateFileTreeHighlight(panelEl);

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

            var origChildren = [];
            for(var i = 0; i < btn.childNodes.length; i++) origChildren.push(btn.childNodes[i]);
            var resetButton = function(ok) {
                while(btn.firstChild) btn.removeChild(btn.firstChild);
                var svgNS = "http://www.w3.org/2000/svg";
                var icon = document.createElementNS(svgNS, "svg");
                icon.setAttribute("width", "14");
                icon.setAttribute("height", "14");
                icon.setAttribute("viewBox", "0 0 24 24");
                icon.setAttribute("fill", "none");
                icon.setAttribute("stroke", ok ? "#4CAF50" : "#CD1818");
                icon.setAttribute("stroke-width", "3");
                icon.setAttribute("stroke-linecap", "round");
                icon.setAttribute("stroke-linejoin", "round");
                var p = document.createElementNS(svgNS, "path");
                p.setAttribute("d", ok ? "M5 13 L10 18 L19 6" : "M6 6 L18 18 M6 18 L18 6");
                icon.appendChild(p);
                btn.appendChild(icon);
                setTimeout(function() {
                    while(btn.firstChild) btn.removeChild(btn.firstChild);
                    for(var j = 0; j < origChildren.length; j++) btn.appendChild(origChildren[j]);
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
                                resetButton(true);
                            }).catch(function() {
                                resetButton(false);
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
                                resetButton(true);
                            } catch(err) {
                                resetButton(false);
                            }
                            document.body.removeChild(ta);
                        }
                    } catch(err) {
                        resetButton(false);
                    }
                }
                else {
                    resetButton(false);
                }
            };
            xhr.open("POST", tracyExceptionsViewer.currentURL, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.send("filePath=" + encodeURIComponent(mdPath) + "&csrfToken=" + encodeURIComponent(tracyExceptionsViewer.csrfToken));
        },

        loadExceptionFile: function(filePath, panelEl) {
            if(!panelEl) panelEl = tracyExceptionLoader.getMainPanel();
            if(!panelEl) {
                window.requestAnimationFrame(function() { tracyExceptionLoader.loadExceptionFile(filePath); });
                return;
            }
            if(panelEl.classList.contains("tracy-mode-window")) {
                this.populateExceptionViewer(filePath, panelEl);
            }
            else {
                if(!window.Tracy.Debug.panels || !window.Tracy.Debug.panels[panelEl.id]) {
                    window.requestAnimationFrame(function() { tracyExceptionLoader.loadExceptionFile(filePath, panelEl); });
                }
                else {
                    var panel = window.Tracy.Debug.panels[panelEl.id];
                    if(panel.elem.dataset.tracyContent) {
                        panel.init();
                    }
                    this.populateExceptionViewer(filePath, panelEl);
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
                if (!e.target) return;

                var mdBtn = e.target.closest && e.target.closest(".tracy-md-copy-btn");
                if (mdBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    tracyExceptionLoader.copyMarkdown(mdBtn);
                    return;
                }

                if (e.target.id === 'clearException') {
                    var clearPanel = tracyExceptionLoader.findOriginatingPanel(e.target);
                    if (clearPanel) tracyExceptionLoader.clearViewer(clearPanel);
                    return;
                }

                let curEl = e.target;
                while (curEl && curEl.tagName !== "A") {
                    curEl = curEl.parentNode;
                }

                if (curEl && curEl.href && curEl.href.indexOf("tracyexception://") !== -1) {
                    e.preventDefault();
                    const panelEl = tracyExceptionLoader.findOriginatingPanel(curEl) || tracyExceptionLoader.getMainPanel();
                    const queryStr = curEl.href.split('?')[1];
                    const fullFilePath = tracyExceptionLoader.getFileLineVars(queryStr, "f").replace(/\\/g, "/");
                    tracyExceptionLoader.loadExceptionFile(fullFilePath, panelEl);
                    tracyExceptionLoader.showUnloadButton(panelEl);
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
