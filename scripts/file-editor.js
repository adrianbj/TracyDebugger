if(!tracyFileEditorLoader) {

    var tracyFileEditorLoader = {

        getFileLineVars: function (query,variable) {
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
        },

        initializeEditor: function() {
            if(!document.getElementById("tfe_recently_opened")) {
                window.requestAnimationFrame(tracyFileEditorLoader.initializeEditor);
            }
            else {
                // populate recently opened files select
                var recentlyOpenSelect = document.getElementById("tfe_recently_opened");
                var storedFiles = JSON.parse(localStorage.getItem("tracyFileEditorRecentlyOpen"));
                if(storedFiles) {
                    for(var i = 0; i < storedFiles.length; ++i) {
                        recentlyOpenSelect.options[recentlyOpenSelect.options.length] = new Option(storedFiles[i], storedFiles[i]);
                    }
                }
                var initialFile = document.getElementById('panelTitleFilePath').textContent;
                tracyFileEditorLoader.addRecentlyOpenedFile(initialFile);
            }
        },

        addRecentlyOpenedFile: function(fullFilePath) {
            var storedFilesArr = [];
            var storedFiles = JSON.parse(localStorage.getItem("tracyFileEditorRecentlyOpen"));
            if(storedFiles) storedFilesArr = storedFiles;
            var recentlyOpenSelect = document.getElementById("tfe_recently_opened");
            var alreadyExists = false;
            for(var i = 0; i < recentlyOpenSelect.length; ++i) {
                if(recentlyOpenSelect.options[i].value == fullFilePath) {
                    alreadyExists = true;
                }
            }
            if(!alreadyExists) {
                var opt = new Option(fullFilePath, fullFilePath);
                recentlyOpenSelect.insertBefore(opt, recentlyOpenSelect.firstChild);
                storedFilesArr.unshift(fullFilePath);
                if(storedFilesArr.length > 10) {
                    storedFilesArr.pop();
                    recentlyOpenSelect.options[storedFilesArr.length] = null;
                }
                localStorage.setItem("tracyFileEditorRecentlyOpen", JSON.stringify(storedFilesArr));
            }
            recentlyOpenSelect.value = fullFilePath;
        },

        getCookie: function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        },

        createButton: function(name, value, needsRawCode) {
            var button = '<input type="submit" id="'+name+'" name="'+name+'"';
            if(needsRawCode) button += ' data-raw-file-editor-code="1"';
            button += ' value="'+value+'" />&nbsp;';
            return button;
        },

        generateButtons: function(fileData) {

            if(typeof fileData === 'undefined') return;

            var fileEditorButtons = document.getElementById("fileEditorButtons");

            if(fileData["writeable"]) {
                if(fileData["isTemplateFile"]) {
                    fileEditorButtons.innerHTML =
                        this.createButton('tracyTestTemplateCode', 'Test', true) +
                        this.createButton('tracyChangeTemplateCode', 'Save', true);
                }
                else {
                    fileEditorButtons.innerHTML =
                        this.createButton('tracyTestFileCode', 'Test', true) +
                        this.createButton('tracySaveFileCode', 'Save', true);
                }

                if(this.getCookie('tracyTestFileEditor') || fileData["isTemplateTest"]) {
                    fileEditorButtons.innerHTML += this.createButton('tracyResetTemplateCode', 'Reset', false);
                }
                else if(fileData["backupExists"]) {
                    fileEditorButtons.innerHTML +=
                        this.createButton('tracyRestoreFileEditorBackup', 'Restore Backup', false);
                }
            }
            else {
                fileEditorButtons.innerHTML = "File is not writeable";
            }
        },

        populateFileEditor: function(filePath, line) {
            filePath = filePath.replace(/\\/g, "/");
            if(filePath && filePath.charAt(0) !== "/") filePath = "/" + filePath;
            document.getElementById("fileEditorFilePath").value = filePath;
            document.cookie = "tracyFileEditorFilePath=" + filePath + "; path=/; SameSite=Strict";
            var fePanel = document.getElementById("tracy-debug-panel-ProcessWire-FileEditorPanel");
            if(fePanel) {
                var titleEl = fePanel.querySelector("#panelTitleFilePath");
                if(titleEl) titleEl.textContent = filePath;
            }

            if(typeof tracyFileEditor === "undefined" || !tracyFileEditor.tfe) {
                window.requestAnimationFrame(function() { tracyFileEditorLoader.populateFileEditor(filePath, line); });
            }
            else {
                var xmlhttp;
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if(xmlhttp.readyState !== XMLHttpRequest.DONE) return;

                    var fileData = null;
                    var parseError = null;
                    if(xmlhttp.status == 200 && xmlhttp.responseText !== "" && xmlhttp.responseText !== "[]") {
                        try { fileData = JSON.parse(xmlhttp.responseText); }
                        catch(e) { parseError = e; }
                    }

                    if(fileData) {
                        tracyFileEditorLoader.generateButtons(fileData);
                        tracyFileEditor.tfe.setValue(fileData["contents"]);
                        tracyFileEditor.tfe.gotoLine(line, 0);
                        var fePanel = document.getElementById("tracy-debug-panel-ProcessWire-FileEditorPanel");
                        if(fePanel) {
                            var titleEl = fePanel.querySelector("#panelTitleFilePath");
                            if(titleEl) titleEl.textContent = filePath;
                        }

                        // modelist is loaded asynchronously, so it may not be ready yet — fall back to text mode
                        if(tracyFileEditor.modelist) {
                            var mode = tracyFileEditor.modelist.getModeForPath(filePath).mode;
                            if(mode == 'ace/mode/log') mode = 'ace/mode/text';
                            tracyFileEditor.tfe.session.setMode(mode);
                        }
                        else {
                            tracyFileEditor.tfe.session.setMode('ace/mode/text');
                        }
                    }
                    else {
                        // surface the failure so the title (set immediately on click) doesn't appear in sync
                        // with whatever stale content was baked into the panel by the server-side initial render.
                        // a parse error here typically means the AJAX returned bluescreen HTML — Tracy renders
                        // a bluescreen for the AJAX request because the originating URL still errors during boot.
                        var msg = "// Failed to load file: " + filePath
                            + "\n// HTTP " + xmlhttp.status + " from " + tracyFileEditor.currentUrl;
                        if(parseError) {
                            msg += "\n// Response was not valid JSON (likely a Tracy bluescreen rendered for the AJAX request)."
                                + "\n// The originating URL above still errors during PW boot, so the file editor AJAX hits the same failure.";
                        }
                        else {
                            msg += "\n// (the URL above is the page that errored — file editor AJAX rides the same boot path)";
                        }
                        tracyFileEditor.tfe.setValue(msg);
                        tracyFileEditor.tfe.session.setMode('ace/mode/text');
                    }
                };
                xmlhttp.open("POST", tracyFileEditor.currentUrl, true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("filePath=" + encodeURIComponent(filePath) + "&csrfToken=" + encodeURIComponent(tracyFileEditor.csrfToken));
                init_php_file_tree(filePath);
            }
        },

        loadFileEditor: function(filePath, line) {
            var panelElem = document.getElementById("tracy-debug-panel-ProcessWire-FileEditorPanel");

            if(panelElem && panelElem.classList.contains("tracy-mode-window")) {
                this.populateFileEditor(filePath, line);
            }
            else {
                if(!window.Tracy.Debug.panels || !panelElem) {
                    window.requestAnimationFrame(function() { tracyFileEditorLoader.loadFileEditor(filePath, line); });
                }
                else {
                    var panel = window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-FileEditorPanel"];
                    if(panel.elem.dataset.tracyContent) {
                        panel.init();
                    }
                    this.populateFileEditor(filePath, line);
                    panel.toFloat();
                    panel.focus();
                    tracyFileEditor.resizeAce();
                }
            }
        }
    };
    tracyFileEditorLoader.initializeEditor();

    (function initTracyFileEditorHandler() {
        function setupTracyFileEditorClickHandler() {
            const htmlElement = document.documentElement;
            const doc = htmlElement.classList.contains('tracy-bs-visible') ? document.body : document;

            doc.addEventListener("click", function(e) {
                if (e.target) {
                    let curEl = e.target;

                    while (curEl && curEl.tagName !== "A") {
                        curEl = curEl.parentNode;
                    }

                    if (curEl && curEl.href && curEl.href.indexOf("tracy://") !== -1) {
                        e.preventDefault();

                        const queryStr = curEl.href.split('?')[1];
                        const fullFilePath = tracyFileEditorLoader.getFileLineVars(queryStr, "f").replace(/\\/g, "/");
                        const line = tracyFileEditorLoader.getFileLineVars(queryStr, "l");

                        tracyFileEditorLoader.loadFileEditor(fullFilePath, line);
                        tracyFileEditorLoader.addRecentlyOpenedFile(fullFilePath);
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupTracyFileEditorClickHandler);
        } else {
            setupTracyFileEditorClickHandler();
        }
    })();
}
