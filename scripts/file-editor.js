if(!tracyFileEditorLoader) {
    var tracyFileEditorLoader = {

        getFileLineVars: function (query,variable) {
            var vars = query.replace("?","").split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
        },

        addFileEditorClickEvents: function() {
            if(!document.getElementById("tracy-debug")) {
                window.requestAnimationFrame(tracyFileEditorLoader.addFileEditorClickEvents);
            }
            else {
                document.getElementById("tracy-debug").addEventListener("click", function(e) {
                    if(e.target) {
                        var curEl = e.target;
                        while(curEl && curEl.tagName != "A") {
                            curEl = curEl.parentNode;
                        }
                        if(curEl && curEl.href && curEl.href.indexOf("tracy://") !== -1) {
                            e.preventDefault();
                            var els = document.getElementsByClassName("active");
                            [].forEach.call(els, function (el) {
                                el.classList.remove("active");
                            });
                            curEl.classList.add("active");
                            tracyFileEditorLoader.loadFileEditor(tracyFileEditorLoader.getFileLineVars(curEl.search, "f"), tracyFileEditorLoader.getFileLineVars(curEl.search, "l"));
                        }
                    }
                });
            }
        },

        getCookie: function(name) {
            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");
            if (parts.length == 2) return parts.pop().split(";").shift();
        },

        createButton: function(name, value, onlick) {
            var button = '<input type="submit" id="'+name+'" name="'+name+'"';
            if(onlick) button += ' onclick="tracyFileEditor.getRawFileEditorCode()"';
            button += 'value="'+value+'" />&nbsp;';
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

        loadFileEditor: function(filePath, line) {

            if(document.getElementById("tracy-debug-panel-FileEditorPanel").classList.contains("tracy-mode-window")) {
                populateFileEditor();
            }
            else {
                if(!window.Tracy.Debug.panels || !document.getElementById("tracy-debug-panel-FileEditorPanel")) {
                    window.requestAnimationFrame(loadFileEditor);
                }
                else {
                    var panel = window.Tracy.Debug.panels["tracy-debug-panel-FileEditorPanel"];
                    panel.focus(function() {
                        if(panel.elem.dataset.tracyContent) {
                            panel.init();
                        }
                        panel.toFloat();
                        populateFileEditor();
                    });
                }
            }

            function populateFileEditor() {

                document.getElementById("fileEditorFilePath").value = filePath;
                document.cookie = "tracyFileEditorFilePath=" + filePath + "; path=/";
                document.getElementById("panelTitleFilePath").innerHTML = "/" + filePath;

                if(typeof ace === "undefined" || typeof tracyFileEditor === "undefined" || !tracyFileEditor.tfe) {
                    window.requestAnimationFrame(populateFileEditor);
                }
                else {
                    var xmlhttp;
                    xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if(xmlhttp.readyState == XMLHttpRequest.DONE) {
                            if(xmlhttp.status == 200 && xmlhttp.responseText !== "" && xmlhttp.responseText !== "[]") {
                                var fileData = JSON.parse(xmlhttp.responseText);
                                tracyFileEditorLoader.generateButtons(fileData);
                                tracyFileEditor.tfe.setValue(fileData["contents"]);
                                tracyFileEditor.tfe.gotoLine(line, 0);

                                // set mode appropriately
                                // in ext-modelist.js I have added "inc" to PHP and "latte" to Twig
                                var mode = tracyFileEditor.modelist.getModeForPath(filePath).mode;
                                tracyFileEditor.tfe.session.setMode(mode);
                            }
                            xmlhttp.getAllResponseHeaders();
                        }
                    };
                    xmlhttp.open("POST", "./", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("filePath=" + filePath);
                }
            }
        }
    };
    tracyFileEditorLoader.addFileEditorClickEvents();
}