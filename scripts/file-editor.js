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
                var initialFile = document.getElementById('panelTitleFilePath').innerHTML;
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

        populateFileEditor: function(filePath, line) {

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
                init_php_file_tree(filePath);
            }
        },

        loadFileEditor: function(filePath, line) {

            if(document.getElementById("tracy-debug-panel-FileEditorPanel").classList.contains("tracy-mode-window")) {
                this.populateFileEditor(filePath, line);
            }
            else {
                if(!window.Tracy.Debug.panels || !document.getElementById("tracy-debug-panel-FileEditorPanel")) {
                    window.requestAnimationFrame(loadFileEditor);
                }
                else {
                    var panel = window.Tracy.Debug.panels["tracy-debug-panel-FileEditorPanel"];
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

    // click event added to body because of links on bluescreen
    document.body.addEventListener("click", function(e) {
        if(e.target) {
            var curEl = e.target;
            while(curEl && curEl.tagName != "A") {
                curEl = curEl.parentNode;
            }
            if(curEl && curEl.href && curEl.href.indexOf("tracy://") !== -1) {
                e.preventDefault();
                var queryStr = curEl.href.split('?')[1];
                var fullFilePath = tracyFileEditorLoader.getFileLineVars(queryStr, "f");
                tracyFileEditorLoader.loadFileEditor(fullFilePath, tracyFileEditorLoader.getFileLineVars(queryStr, "l"));

                tracyFileEditorLoader.addRecentlyOpenedFile(fullFilePath);

            }
        }
    });
}