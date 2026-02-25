<?php namespace ProcessWire;

use Tracy\Debugger;

class ConsolePanel extends BasePanel {

    private $icon;
    private $iconColor;
    private $tracyIncludeCode;

    public function getTab() {
        if(TracyDebugger::isAdditionalBar()) {
            return;
        }

        Debugger::timer('console');

        $this->tracyIncludeCode = json_decode((string)$this->wire('input')->cookie->tracyIncludeCode, true);
        if($this->tracyIncludeCode && $this->tracyIncludeCode['when'] !== 'off') {
            $this->iconColor = $this->wire('input')->cookie->tracyCodeError ? TracyDebugger::COLOR_ALERT : TracyDebugger::COLOR_WARN;
        }
        else {
            $this->iconColor = TracyDebugger::COLOR_NORMAL;
        }

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                 width="16px" height="13.7px" viewBox="439 504.1 16 13.7" enable-background="new 439 504.1 16 13.7" xml:space="preserve">
            <path class="consoleIconPath" fill="' . $this->iconColor . '" d="M453.9,504.1h-13.7c-0.6,0-1.1,0.5-1.1,1.1v11.4c0,0.6,0.5,1.1,1.1,1.1h13.7c0.6,0,1.1-0.5,1.1-1.1v-11.4
                C455,504.7,454.5,504.1,453.9,504.1z M441.3,512.1l2.3-2.3l-2.3-2.3l1.1-1.1l3.4,3.4l-3.4,3.4L441.3,512.1z M450.4,513.3h-4.6v-1.1
                h4.6V513.3z"/>
            </svg>';

        return '
        <span title="Console">
            ' . $this->icon . (TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Console' : '') . '
        </span>';
    }


    public function getPanel() {

        $rootPath = $this->wire('config')->paths->root;
        $currentUrl = $_SERVER['REQUEST_URI'];
        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $inAdmin = TracyDebugger::$inAdmin;

        // store various $input properties so they are available to the console
        $this->wire('session')->tracyPostData = $this->wire('input')->post->getArray();
        $this->wire('session')->tracyGetData = $this->wire('input')->get->getArray();
        $this->wire('session')->tracyWhitelistData = $this->wire('input')->whitelist->getArray();

        // generate CSRF token
        if(!$this->wire('session')->tracyConsoleToken) {
            $this->wire('session')->tracyConsoleToken = bin2hex(random_bytes(32));
        }
        $csrfToken = $this->wire('session')->tracyConsoleToken;

        if(TracyDebugger::getDataValue('referencePageEdited') && $this->wire('input')->get('id') &&
            ($this->wire('process') == 'ProcessPageEdit' ||
                $this->wire('process') == 'ProcessUser' ||
                $this->wire('process') == 'ProcessRole' ||
                $this->wire('process') == 'ProcessPermission' ||
                $this->wire('process') == 'ProcessLanguage'
            )
        ) {
            $p = $this->wire('process')->getPage();
            if($p instanceof NullPage) {
                $p = $this->wire('pages')->get((int) $this->wire('input')->get('id'));
            }
        }
        else {
            $p = $this->wire('page');
        }

        $pid = $p ? $p->id : 'null';

        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessField') {
            $fid = (int) $this->wire('input')->get('id');
        }
        else {
            $fid = null;
        }
        if($this->wire('input')->get('id') && $this->wire('page')->process == 'ProcessTemplate') {
            $tid = (int) $this->wire('input')->get('id');
        }
        else {
            $tid = null;
        }
        if($this->wire('input')->get('name') && $this->wire('page')->process == 'ProcessModule') {
            $mid = $this->wire('sanitizer')->name($this->wire('input')->get('name'));
        }
        else {
            $mid = null;
        }

        $file = $this->wire('config')->paths->cache . 'TracyDebugger/consoleCode.php';
        if(file_exists($file)) {
            $code = file_get_contents($file);
            $code = implode("\n", array_slice(explode("\n", $code), 1));
            // json_encode to convert line breaks to \n - needed by setValue()
            $code = json_encode($code);
        }
        else {
            $code = '""';
        }

        // get snippets from filesystem
        $snippets = array();
        $snippetsPath = TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/';
        if(file_exists($this->wire('config')->paths->site.$snippetsPath)) {
            $snippetFiles = new \DirectoryIterator($this->wire('config')->paths->site.$snippetsPath);
            $i=0;
            foreach($snippetFiles as $snippetFile) {
                if(!$snippetFile->isDot() && $snippetFile->isFile()) {
                    $snippetFileName = $snippetFile->getPathname();
                    $snippets[$i]['name'] = pathinfo($snippetFileName, PATHINFO_BASENAME);
                    $snippets[$i]['filename'] = $snippetFileName;
                    $snippets[$i]['code'] = str_replace(TracyDebugger::getDataValue('consoleCodePrefix'), '', file_get_contents($snippetFileName));
                    $snippets[$i]['modified'] = filemtime($snippetFileName);
                    $i++;
                }
            }
            $snippets = json_encode($snippets);
        }
        if(!$snippets) $snippets = json_encode(array());

        $out = '<script>' . file_get_contents($this->wire('config')->paths->TracyDebugger . 'scripts/get-query-variable.js') . '</script>';

        $maximizeSvg =
        '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             viewBox="282.8 231 16 15.2" enable-background="new 282.8 231 16 15.2" xml:space="preserve">
            <polygon fill="#AEAEAE" points="287.6,233.6 298.8,231 295.4,242 "/>
            <polygon fill="#AEAEAE" points="293.9,243.6 282.8,246.2 286.1,235.3 "/>
        </svg>';

        $codeUseSoftTabs = TracyDebugger::getDataValue('codeUseSoftTabs');
        $codeShowInvisibles = TracyDebugger::getDataValue('codeShowInvisibles');
        $codeTabSize = TracyDebugger::getDataValue('codeTabSize');
        $customSnippetsUrl = TracyDebugger::getDataValue('customSnippetsUrl');

        if(TracyDebugger::getDataValue('pwAutocompletions')) {
            $i=0;
            foreach(TracyDebugger::getApiData('variables') as $key => $vars) {
                foreach($vars as $name => $params) {
                    if(strpos($name, '()') !== false) {
                        $pwAutocompleteArr[$i]['name'] = "$$key->" . str_replace('___', '', $name) . ($this->wire()->$key && method_exists($this->wire()->$key, $name) ? '()' : '');
                        $pwAutocompleteArr[$i]['meta'] = 'PW method';
                    }
                    else {
                        $pwAutocompleteArr[$i]['name'] = "$$key->" . str_replace('___', '', $name);
                        $pwAutocompleteArr[$i]['meta'] = 'PW property';
                    }
                    if(TracyDebugger::getDataValue('codeShowDescription')) {
                        $pwAutocompleteArr[$i]['docHTML'] = $params['description'] . "\n" . (isset($params['params']) && !empty($params['params']) ? '('.implode(', ', $params['params']).')' : '');
                    }
                    $i++;
                }
            }

            $i=0;
            foreach(TracyDebugger::getApiData('proceduralFunctions') as $key => $vars) {
                foreach($vars as $name => $params) {
                    $pwAutocompleteArr[$i]['name'] = $name . '()';
                    $pwAutocompleteArr[$i]['meta'] = 'PW function';
                    if(TracyDebugger::getDataValue('codeShowDescription')) {
                        $pwAutocompleteArr[$i]['docHTML'] = $params['description'] . "\n" . (isset($params['params']) && !empty($params['params']) ? '('.implode(', ', $params['params']).')' : '');
                    }
                    $i++;
                }
            }

            // page fields
            $i = count($pwAutocompleteArr);
            if($p && !$p instanceof NullPage) {
                foreach($p->fields as $field) {
                    $pwAutocompleteArr[$i]['name'] = '$page->'.$field;
                    $pwAutocompleteArr[$i]['meta'] = 'PW ' . str_replace('Fieldtype', '', $field->type) . ' field';
                    if(TracyDebugger::getDataValue('codeShowDescription')) $pwAutocompleteArr[$i]['docHTML'] = $field->description;
                    $i++;
                }
            }
            $pwAutocomplete = json_encode($pwAutocompleteArr);
        }
        else {
            $pwAutocomplete = json_encode(array());
        }

        $aceTheme = TracyDebugger::getDataValue('aceTheme');
        $codeFontSize = TracyDebugger::getDataValue('codeFontSize');
        $codeLineHeight = TracyDebugger::getDataValue('codeLineHeight');
        $externalEditorLink = str_replace('"', "'", TracyDebugger::createEditorLink($this->wire('config')->paths->site.TracyDebugger::getDataValue('snippetsPath').'/TracyDebugger/snippets/'.'ExternalEditorDummyFile', 0, '&#xf040;', 'Edit in external editor'));
        $colorNormal = TracyDebugger::COLOR_NORMAL;
        $colorWarn = TracyDebugger::COLOR_WARN;

        $dbRestoreMessageSafe = isset($this->dbRestoreMessage) ? htmlspecialchars($this->dbRestoreMessage, ENT_QUOTES, 'UTF-8') : '';
        $tracyCodeErrorSafe = $this->wire('input')->cookie->tracyCodeError
            ? htmlspecialchars($this->wire('input')->cookie->tracyCodeError, ENT_QUOTES, 'UTF-8')
            : '';

        $out .= <<< HTML
        <script>

        function escapeHtml(str) {
            if (typeof str !== 'string') return str;
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Store event listeners for cleanup
        const listenerMap = new Map();

        let db = null;
        let dbReady = false;
        let dbReadyPromise = initializeDB();

        function initializeDB() {
            return new Promise(function(resolve, reject) {
                const dbRequest = indexedDB.open('TracyConsole', 1);
                dbRequest.onupgradeneeded = function(event) {
                    db = event.target.result;
                    if (!db.objectStoreNames.contains('tabs')) {
                        const tabStore = db.createObjectStore('tabs', { keyPath: 'id' });
                        tabStore.createIndex('name', 'name', { unique: false });
                    }
                    if (!db.objectStoreNames.contains('snippets')) {
                        const snippetStore = db.createObjectStore('snippets', { keyPath: 'name' });
                        snippetStore.createIndex('modified', 'modified', { unique: false });
                    }
                };
                dbRequest.onsuccess = function(event) {
                    db = event.target.result;
                    dbReady = true;
                    resolve(db);
                };
                dbRequest.onerror = function(event) {
                    console.error('Database initialization failed:', event.target.error);
                    reject(event.target.error);
                };
            });
        }

        var tracyConsole = {
            tce: {},
            tracyModuleUrl: "$tracyModuleUrl",
            csrfToken: "$csrfToken",
            tabsContainer: null,
            addTabButton: null,
            currentTabId: null,
            maxHistoryItems: 25,
            desc: false,
            loadingSnippet: false,
            inAdmin: "$inAdmin",
            customSnippetsUrl: "$customSnippetsUrl",
            snippetsPath: "$snippetsPath",
            rootPath: "$rootPath",
            pwAutocomplete: $pwAutocomplete,
            aceTheme: "$aceTheme",
            codeFontSize: $codeFontSize,
            lineHeight: $codeLineHeight,
            externalEditorLink: "$externalEditorLink",
            colorNormal: "$colorNormal",
            colorWarn: "$colorWarn",
            split: null,
            consoleGutterSize: 8,
            minSize: null,
            scrollSaveTimer: null,
            scrollSaveDelay: 500,

            // Returns a resolved promise with the open db, retrying if needed.
            waitForDB: async function(maxRetries = 3, retryDelay = 1000) {
                for (let attempt = 0; attempt < maxRetries; attempt++) {
                    try {
                        if (dbReady && db && db.objectStoreNames.length > 0) {
                            return db;
                        }
                        console.log('waitForDB: Attempting to initialize database');
                        const database = await dbReadyPromise;
                        if (!db || db.objectStoreNames.length === 0) {
                            throw new Error('Database not initialized or closed after dbReadyPromise');
                        }
                        console.log('waitForDB: Database initialized successfully');
                        return db;
                    } catch (err) {
                        if (attempt >= maxRetries - 1) {
                            console.error('waitForDB: Max retries ' + maxRetries + ' reached, giving up');
                            throw new Error('Failed to initialize database after multiple attempts');
                        }
                        console.warn('waitForDB: Attempt ' + (attempt + 1) + ' failed, retrying in ' + retryDelay + 'ms');
                        await new Promise(resolve => setTimeout(resolve, retryDelay));
                        dbReady = false;
                        db = null;
                        dbReadyPromise = initializeDB();
                    }
                }
                throw new Error('waitForDB: Unexpected exit from retry loop');
            },

            // get a single record from a store by key.
            dbGet: async function(storeName, key) {
                const database = await this.waitForDB();
                return new Promise(function(resolve, reject) {
                    const tx = database.transaction([storeName], 'readonly');
                    tx.objectStore(storeName).get(key).onsuccess = e => resolve(e.target.result);
                    tx.onerror = reject;
                });
            },

            // get all records from a store (excluding sentinel keys).
            dbGetAll: async function(storeName, excludeKey) {
                const database = await this.waitForDB();
                return new Promise(function(resolve, reject) {
                    const tx = database.transaction([storeName], 'readonly');
                    tx.objectStore(storeName).getAll().onsuccess = function(e) {
                        let results = e.target.result || [];
                        if (excludeKey !== undefined) {
                            results = results.filter(item => item.id !== excludeKey && item.name !== excludeKey);
                        }
                        resolve(results);
                    };
                    tx.onerror = reject;
                });
            },

            // put one or more records in a store.
            // records can be a single object or an array of objects.
            dbPut: async function(storeName, records) {
                if (!Array.isArray(records)) records = [records];
                const database = await this.waitForDB();
                return new Promise(function(resolve, reject) {
                    const tx = database.transaction([storeName], 'readwrite');
                    const store = tx.objectStore(storeName);
                    records.forEach(r => store.put(r));
                    tx.oncomplete = resolve;
                    tx.onerror = reject;
                });
            },

            // delete a record from a store by key.
            dbDelete: async function(storeName, key) {
                const database = await this.waitForDB();
                return new Promise(function(resolve, reject) {
                    const tx = database.transaction([storeName], 'readwrite');
                    tx.objectStore(storeName).delete(key);
                    tx.oncomplete = resolve;
                    tx.onerror = reject;
                });
            },

            // selected-tab tracking
            getSelectedTabId: function() {
                const v = localStorage.getItem('tracyConsoleSelectedTab');
                return v ? Number(v) : 1;
            },
            setSelectedTabId: function(id) {
                localStorage.setItem('tracyConsoleSelectedTab', String(id));
            },

            isSafari: function() {
                return navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1;
            },

            getCookie: function(name) {
                const value = "; " + document.cookie;
                const parts = value.split("; " + name + "=");
                if (parts.length == 2) return parts.pop().split(";").shift();
            },

            disableButton: function(button) {
                const el = document.getElementById(button);
                if (el) {
                    el.setAttribute("disabled", true);
                    el.classList.add("disabledButton");
                }
            },

            enableButton: function(button) {
                const el = document.getElementById(button);
                if (el) {
                    el.removeAttribute("disabled");
                    el.classList.remove("disabledButton");
                }
            },

            tryParseJSON: function(str) {
                if(!isNaN(str)) return str;
                try {
                    const o = JSON.parse(str);
                    if(o && typeof o === "object" && o !== null) {
                        if(o.message.indexOf("Compiled file") > -1) {
                            return "";
                        }
                        else {
                            return "Error: " + escapeHtml(o.message);
                        }
                    }
                }
                catch(e) {
                    return str;
                }
                return false;
            },

            migrateLocalStorageToIndexedDB: async function() {
                const tracyConsoleTabs = localStorage.getItem("tracyConsoleTabs");
                // if no LocalStorage data, resolve immediately
                if (!tracyConsoleTabs) {
                    return;
                }
                const snippets = localStorage.getItem("tracyConsoleSnippets");

                const database = await new Promise(function(resolve, reject) {
                    const request = indexedDB.open('TracyConsole', 1);
                    request.onerror = () => reject(new Error('Failed to open IndexedDB'));

                    request.onupgradeneeded = function(event) {
                        const migrateDb = event.target.result;
                        if (!migrateDb.objectStoreNames.contains('tabs')) {
                            migrateDb.createObjectStore('tabs', { keyPath: 'id' });
                        }
                        if (!migrateDb.objectStoreNames.contains('snippets')) {
                            migrateDb.createObjectStore('snippets', { keyPath: 'name' });
                        }
                    };

                    request.onsuccess = event => resolve(event.target.result);
                });

                const count = await new Promise(function(resolve, reject) {
                    const checkTx = database.transaction(['tabs'], 'readonly');
                    const countRequest = checkTx.objectStore('tabs').count();
                    countRequest.onsuccess = () => resolve(countRequest.result);
                    countRequest.onerror = () => reject(new Error('Failed to check tab count'));
                });

                // migrateLocalStorageToIndexedDB: IndexedDB already contains tabs, skipping migration
                if (count > 0) {
                    database.close();
                    return;
                }

                let tabs = [];
                try {
                    tabs = JSON.parse(tracyConsoleTabs) || [];
                } catch (e) {
                    console.warn('migrateLocalStorageToIndexedDB: Invalid tracyConsoleTabs JSON:', e);
                    database.close();
                    return;
                }

                await new Promise((resolve, reject) => {
                    const tx = database.transaction(['tabs', 'snippets'], 'readwrite');
                    const tabStore = tx.objectStore('tabs');
                    const snippetStore = tx.objectStore('snippets');

                    tabs.forEach(tab => {
                        const tabData = {
                            id: Number(tab.id) || 1,
                            name: tab.name || 'Untitled-' + this.getNextTabNumber(),
                            code: tab.code || '',
                            historyData: tab.historyData || [],
                            historyItem: Number(tab.historyItem) || 0,
                            historyCount: Number(tab.historyCount) || 0,
                            result: tab.result || '',
                            selections: tab.selections || {},
                            scrollTop: Number(tab.scrollTop) || 0,
                            scrollLeft: Number(tab.scrollLeft) || 0,
                            splitSizes: tab.splitSizes || [40, 60]
                        };
                        tabStore.put(tabData);
                    });

                    if (snippets) {
                        try {
                            const snippetData = JSON.parse(snippets) || [];
                            snippetData.forEach(snippet => {
                                snippetStore.put({
                                    name: snippet.name,
                                    code: snippet.code,
                                    modified: snippet.modified || Date.now()
                                });
                            });
                        } catch (e) {
                            console.warn('migrateLocalStorageToIndexedDB: Invalid tracyConsoleSnippets JSON:', e);
                        }
                    }

                    // clear old LocalStorage keys
                    localStorage.removeItem('tracyConsoleTabs');
                    localStorage.removeItem('tracyConsoleSnippets');
                    localStorage.removeItem('tracyConsole');
                    localStorage.removeItem('tracyConsoleHistory');
                    localStorage.removeItem('tracyConsoleHistoryCount');
                    localStorage.removeItem('tracyConsoleHistoryItem');
                    localStorage.removeItem('tracyConsoleResults');
                    localStorage.removeItem('tracyConsoleSplitSizes');

                    tx.oncomplete = () => { database.close(); resolve(); };
                    tx.onerror = () => {
                        console.error('migrateLocalStorageToIndexedDB: Transaction failed');
                        database.close();
                        reject(new Error('Transaction failed'));
                    };
                });
            },

            // Save the current tab state to IndexedDB.
            // Accepts an optional pre-fetched existingTab to avoid a redundant read.
            saveToIndexedDB: async function(existingTab) {
                try {
                    await this.waitForDB();

                    const code = this.tce.getValue();
                    const selections = this.tce.selection.toJSON();

                    // Only read existing tab if not supplied by the caller.
                    if (existingTab === undefined) {
                        existingTab = await this.dbGet('tabs', this.currentTabId);
                    }

                    const name = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label')?.textContent || 'Untitled';

                    const tabElements = Array.from(document.querySelectorAll('#tracyTabs button[data-tab-id]'));
                    const currentIndex = tabElements.findIndex(el => parseInt(el.dataset.tabId) === tracyConsole.currentTabId);

                    const tab = {
                        id: this.currentTabId,
                        name: name,
                        code: code,
                        historyData: existingTab ? existingTab.historyData : [],
                        historyItem: existingTab ? existingTab.historyItem : null,
                        historyCount: existingTab ? existingTab.historyCount : 0,
                        result: document.getElementById("tracyConsoleResult")?.innerHTML || '',
                        selections: selections,
                        scrollTop: this.tce.session.getScrollTop(),
                        scrollLeft: this.tce.session.getScrollLeft(),
                        splitSizes: existingTab ? existingTab.splitSizes : [40, 60],
                        order: currentIndex >= 0 ? currentIndex : (existingTab?.order || 0)
                    };

                    await this.dbPut('tabs', tab);
                    this.setSelectedTabId(this.currentTabId);
                    return true;
                } catch (err) {
                    console.warn('Error saving to IndexedDB:', err);
                    throw err;
                }
            },

            // debounced scroll save — prevents per-tick DB writes
            // on changeScrollTop/changeScrollLeft.
            scheduleSave: function() {
                if (this.scrollSaveTimer) clearTimeout(this.scrollSaveTimer);
                this.scrollSaveTimer = setTimeout(() => {
                    tracyConsole.scrollSaveTimer = null;
                    tracyConsole.saveToIndexedDB().catch(err => console.warn('Error in scheduled save:', err));
                }, this.scrollSaveDelay);
            },

            saveSplits: async function() {
                if (this.split && typeof this.split.getSizes === 'function') {
                    const splits = this.split.getSizes();
                    try {
                        const existingTab = await this.dbGet('tabs', this.currentTabId);
                        if (existingTab) {
                            existingTab.splitSizes = splits;
                            await this.dbPut('tabs', existingTab);
                        }
                    } catch (err) {
                        console.warn('Error saving splits:', err);
                    }
                }
            },

            getSplits: async function() {
                try {
                    const tab = await this.dbGet('tabs', this.currentTabId);
                    return tab?.splitSizes || [40, 60];
                } catch (err) {
                    return [40, 60];
                }
            },

            setEditorState: function(data) {
                if (data) {
                    // Restore split sizes FIRST so the editor is the correct height
                    // before setValue triggers rendering — otherwise Ace calculates
                    // max scroll based on the wrong editor height and clamps the value.
                    if (data.splitSizes) {
                        if (!this.split) {
                            if (!this.minSize) {
                                this.minSize = this.lineHeight;
                            }
                            this.split = Split(['#tracyConsoleCode', '#tracyConsoleResult'], {
                                direction: 'vertical',
                                cursor: 'row-resize',
                                sizes: data.splitSizes,
                                minSize: this.minSize,
                                expandToMin: true,
                                gutterSize: this.consoleGutterSize,
                                snapOffset: 10,
                                dragInterval: this.lineHeight,
                                gutterAlign: 'end',
                                onDrag: this.resizeAce,
                                onDragEnd: function() {
                                    tracyConsole.saveSplits();
                                    tracyConsole.tce.focus();
                                }
                            });
                        }
                        else {
                            this.split.setSizes(data.splitSizes);
                        }
                        // Force Ace to recalculate its dimensions at the new split size
                        // before we load content and restore scroll.
                        this.tce.resize(true);
                    }

                    this.tce.setValue(data.code || '', -1);

                    // Validate selections object before attempting to restore
                    if (data.selections && typeof data.selections === 'object' && data.selections.ranges) {
                        try {
                            this.tce.selection.fromJSON(data.selections);
                        } catch (e) {
                            console.warn('Failed to restore selections:', e);
                            // Fall back to default cursor position
                            this.tce.clearSelection();
                        }
                    } else {
                        // No valid selections data, just clear selection
                        this.tce.clearSelection();
                    }

                    if (data.scrollTop !== undefined) {
                        this.tce.session.setScrollTop(data.scrollTop);
                    }
                    if (data.scrollLeft !== undefined) {
                        this.tce.session.setScrollLeft(data.scrollLeft);
                    }
                }
                else {
                    this.tce.setValue('', -1);
                }
                this.tce.focus();
            },

            clearResults: async function() {
                const resultsDiv = document.getElementById("tracyConsoleResult");
                const statusDiv = document.getElementById("tracyConsoleStatus");
                if (resultsDiv) resultsDiv.innerHTML = "";
                if (statusDiv) statusDiv.innerHTML = "";

                // Update only the result field of the current tab.
                try {
                    const tab = await this.dbGet('tabs', this.currentTabId);
                    if (tab) {
                        tab.result = '';
                        await this.dbPut('tabs', tab);
                    }
                } catch (err) {
                    console.warn('Error clearing results:', err);
                }

                this.tce.focus();
            },

            processTracyCode: function() {
                const code = this.tce.getSelectedText() || this.tce.getValue();
                const statusDiv = document.getElementById("tracyConsoleStatus");
                if (statusDiv) {
                    statusDiv.innerHTML = "<span style='font-family: FontAwesome !important' class='fa fa-spinner fa-spin'></span> Processing";
                }
                const codeReturn = this.getCookie('tracyIncludeCode') ? false : true;
                this.callPhp(code, codeReturn);
                this.saveHistory();
                this.tce.focus();
            },

            reloadAndRun: async function() {
                try {
                    const result = await this.dbGet('snippets', 'selectedSnippet');
                    const snippetName = result?.value;
                    if (snippetName) {
                        const statusDiv = document.getElementById("tracyConsoleStatus");
                        if (statusDiv) {
                            statusDiv.innerHTML = "<span style='font-family: FontAwesome !important' class='fa fa-spinner fa-spin'></span> Processing";
                        }
                        this.reloadSnippet(true);
                    }
                    else {
                        this.processTracyCode();
                    }
                } catch (err) {
                    console.warn('Error in reloadAndRun:', err);
                    this.processTracyCode();
                }
            },

            tracyIncludeCode: function(when) {
                when = when.value;
                const params = {when: when, pid: $pid};
                const icons = document.getElementsByClassName("consoleIconPath");
                let i = 0;
                if (when === 'off') {
                    document.cookie = "tracyIncludeCode=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Strict";
                    while (i < icons.length) {
                        icons[i].style.fill = this.colorNormal;
                        i++;
                    }
                    const runInjectButton = document.getElementById("runInjectButton");
                    if (runInjectButton) runInjectButton.value = 'Run';
                }
                else {
                    const expires = new Date();
                    expires.setMinutes(expires.getMinutes() + 5);
                    document.cookie = "tracyIncludeCode="+JSON.stringify(params)+"; expires="+expires.toGMTString()+";path=/; SameSite=Strict";
                    while (i < icons.length) {
                        icons[i].style.fill = this.colorWarn;
                        i++;
                    }
                    const runInjectButton = document.getElementById("runInjectButton");
                    if (runInjectButton) runInjectButton.value = 'Inject';
                }
                this.tce.focus();
            },

            toggleSnippetsPane: function() {
                if (this.getCookie('tracySnippetsPaneCollapsed') == 1) {
                    document.cookie = "tracySnippetsPaneCollapsed=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Strict";
                    const mainContainer = document.getElementById("tracyConsoleMainContainer");
                    const snippetPaneToggle = document.getElementById("snippetPaneToggle");
                    const snippetsContainer = document.getElementById("tracySnippetsContainer");
                    if (mainContainer) mainContainer.style.width = "calc(100% - 290px)";
                    if (snippetPaneToggle) snippetPaneToggle.style.right = "-290px";
                    if (snippetsContainer) snippetsContainer.classList.remove('tracyHidden');
                    if (snippetPaneToggle) snippetPaneToggle.innerHTML = "&#xf054;";
                }
                else {
                    const expires = new Date();
                    expires.setMinutes(expires.getMinutes() + (10 * 365 * 24 * 60));
                    document.cookie = "tracySnippetsPaneCollapsed=1; expires="+expires.toGMTString()+"; path=/; SameSite=Strict";
                    const mainContainer = document.getElementById("tracyConsoleMainContainer");
                    const snippetPaneToggle = document.getElementById("snippetPaneToggle");
                    const snippetsContainer = document.getElementById("tracySnippetsContainer");
                    if (mainContainer) mainContainer.style.width = "100%";
                    if (snippetPaneToggle) snippetPaneToggle.style.right = "0";
                    if (snippetsContainer) snippetsContainer.classList.add('tracyHidden');
                    if (snippetPaneToggle) snippetPaneToggle.innerHTML = "&#xf053;";
                }
            },

            toggleKeyboardShortcuts: function() {
                const shortcutsDiv = document.getElementById("consoleKeyboardShortcuts");
                if (shortcutsDiv) shortcutsDiv.classList.toggle('tracyHidden');
            },

            toggleFullscreen: function() {
                const tracyConsolePanel = document.getElementById('tracy-debug-panel-ProcessWire-ConsolePanel');
                const consoleContainer = document.getElementById("tracyConsoleContainer");
                if (tracyConsolePanel && consoleContainer) {
                    if (!consoleContainer.classList.contains("maximizedConsole")) {
                        window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-ConsolePanel"].toFloat();
                        tracyConsolePanel.style.resize = 'none';
                        if (this.isSafari()) {
                            document.body.appendChild(tracyConsolePanel);
                        }
                    }
                    else {
                        tracyConsolePanel.style.resize = 'both';
                        if (this.isSafari()) {
                            document.getElementById("tracy-debug").appendChild(tracyConsolePanel);
                        }
                    }
                    consoleContainer.classList.toggle("maximizedConsole");
                    document.documentElement.classList.toggle('noscroll');
                    this.resizeAce();
                }
            },

            callPhp: function(code, codeReturn = true) {
                if (!codeReturn) {
                    const expires = new Date();
                    expires.setMinutes(expires.getMinutes() + 5);
                    document.cookie = "tracyCodeReturn=no;expires="+expires.toGMTString()+";path=/";
                }
                else {
                    document.cookie = "tracyCodeReturn=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                }

                const xmlhttp = new XMLHttpRequest();
                const onReadyStateChange = function() {
                    if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                        const statusDiv = document.getElementById("tracyConsoleStatus");
                        if (statusDiv) {
                            statusDiv.innerHTML = "✔ " + (codeReturn ? "Executed" : "Injected @ " + escapeHtml(JSON.parse(tracyConsole.getCookie('tracyIncludeCode')).when));
                        }
                        const resultsDiv = document.getElementById("tracyConsoleResult");
                        if (xmlhttp.status == 200 && resultsDiv) {
                            const resultId = Date.now();
                            resultsDiv.innerHTML += '<div id="tracyConsoleResult_'+resultId+'" style="padding:10px 0">' + tracyConsole.tryParseJSON(xmlhttp.responseText) + '</div>';

                            tracyConsole.saveToIndexedDB().catch(err => console.warn('Error updating tab result:', err));

                            const resultElement = document.getElementById("tracyConsoleResult_"+resultId);
                            if (resultElement) resultElement.scrollIntoView();
                            if (!document.getElementById("tracy-debug-panel-ProcessWire-ConsolePanel").classList.contains("tracy-mode-float")) {
                                window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-ConsolePanel"].toFloat();
                            }
                        }
                        else if (resultsDiv) {
                            const errorStr = escapeHtml(xmlhttp.status + ': ' + xmlhttp.statusText) + '<br />' + escapeHtml(xmlhttp.responseText);
                            resultsDiv.innerHTML = '<div style="padding: 10px 0">' + errorStr + '</div><div style="position:relative; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';

                            const errorExpires = new Date();
                            errorExpires.setMinutes(errorExpires.getMinutes() + (10 * 365 * 24 * 60));
                            document.cookie = "tracyCodeError=" + encodeURIComponent(errorStr) + ";expires="+errorExpires.toGMTString()+";path=/";
                        }
                        listenerMap.delete(xmlhttp);
                        xmlhttp.getAllResponseHeaders();
                    }
                };
                xmlhttp.onreadystatechange = onReadyStateChange;
                listenerMap.set(xmlhttp, { onreadystatechange: onReadyStateChange });

                const dbBackup = document.getElementById("dbBackup")?.checked || false;
                const allowBluescreen = document.getElementById("allowBluescreen")?.checked || false;
                const backupFilename = encodeURIComponent(document.getElementById("backupFilename")?.value || '');
                const accessTemplateVars = !this.inAdmin ? document.getElementById("accessTemplateVars")?.checked || false : "false";

                xmlhttp.open("POST", "$currentUrl", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xmlhttp.send("tracyConsole=1&csrfToken=" + encodeURIComponent(this.csrfToken) + "&codeReturn=" + encodeURIComponent(codeReturn) + "&allowBluescreen=" + encodeURIComponent(allowBluescreen) +
                    "&dbBackup=" + encodeURIComponent(dbBackup) + "&backupFilename=" + encodeURIComponent(backupFilename) +
                    "&accessTemplateVars=" + encodeURIComponent(accessTemplateVars) + "&pid=" + encodeURIComponent($pid) +
                    "&fid=" + encodeURIComponent($fid) + "&tid=" + encodeURIComponent($tid) + "&mid=" + encodeURIComponent($mid) +
                    "&code=" + encodeURIComponent(code));
            },

            resizeAce: function(focus = true) {
                if (this.tce) {
                    this.tce.resize(true);
                    if (focus) {
                        window.Tracy.Debug.panels["tracy-debug-panel-ProcessWire-ConsolePanel"].focus();
                        this.tce.focus();
                    }
                }
            },

            getSnippet: function(name, process = false) {
                return new Promise(function(resolve, reject) {
                    const xmlhttp = new XMLHttpRequest();
                    const onReadyStateChange = function() {
                        if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                            if (xmlhttp.status == 200 && xmlhttp.responseText !== "[]") {
                                tracyConsole.tce.setValue(xmlhttp.responseText);
                                tracyConsole.tce.gotoLine(0, 0);

                                if (process) tracyConsole.processTracyCode();

                                tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ext-modelist.js", function() {
                                    tracyConsole.modelist = ace.require("ace/ext/modelist");
                                    let mode = tracyConsole.modelist.getModeForPath(tracyConsole.rootPath + tracyConsole.snippetsPath + name).mode;

                                    if (xmlhttp.responseText.indexOf('<?php') !== -1) {
                                        mode = 'ace/mode/php';
                                    }
                                    else {
                                        mode = mode == 'ace/mode/php' ? { path: "ace/mode/php", inline: true } : mode;
                                    }

                                    tracyConsole.tce.session.setMode(mode);
                                    resolve();
                                });
                            }
                            else {
                                reject(new Error("Failed to load snippet: " + xmlhttp.status));
                            }
                            listenerMap.delete(xmlhttp);
                        }
                    };
                    xmlhttp.onreadystatechange = onReadyStateChange;
                    listenerMap.set(xmlhttp, { onreadystatechange: onReadyStateChange });

                    xmlhttp.open("POST", "$currentUrl", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send("tracysnippets=1&csrfToken=" + encodeURIComponent(tracyConsole.csrfToken) + "&snippetname=" + encodeURIComponent(name));
                });
            },

            getAllSnippets: async function() {
                // Exclude the 'selectedSnippet' sentinel by its keyPath value ('name').
                return this.dbGetAll('snippets', 'selectedSnippet');
            },

            getSnippetItem: async function(name) {
                if (!name) return undefined;
                return this.dbGet('snippets', name);
            },

            modifyConsoleSnippets: async function(tracySnippetName, code, deleteSnippet) {
                if (deleteSnippet) {
                    if (!confirm("Are you sure you want to delete the \"" + tracySnippetName + "\" snippet?")) return false;
                }

                try {
                    if (deleteSnippet) {
                        await this.dbDelete('snippets', tracySnippetName);
                        this.patchSnippetList(tracySnippetName, null, true);
                    }
                    else {
                        const record = { name: tracySnippetName, code: code, modified: Date.now() };
                        await this.dbPut('snippets', record);
                        this.patchSnippetList(tracySnippetName, record, false);
                        this.setActiveSnippet(tracySnippetName);
                    }

                    // Sync to server
                    const xmlhttp = new XMLHttpRequest();
                    const onReadyStateChange = function() {
                        if (xmlhttp.readyState == XMLHttpRequest.DONE && xmlhttp.status == 200) {
                            // Snippet synced to server
                        }
                        xmlhttp.getAllResponseHeaders();
                    };
                    xmlhttp.onreadystatechange = onReadyStateChange;
                    listenerMap.set(xmlhttp, { onreadystatechange: onReadyStateChange });

                    xmlhttp.open("POST", "$currentUrl", true);
                    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    if (deleteSnippet) {
                        const snippetNameInput = document.getElementById("tracySnippetName");
                        if (snippetNameInput) snippetNameInput.value = '';
                        xmlhttp.send("tracysnippets=1&csrfToken=" + encodeURIComponent(this.csrfToken) + "&snippetname=" + encodeURIComponent(tracySnippetName) + "&deletesnippet=1");
                    }
                    else {
                        xmlhttp.send("tracysnippets=1&csrfToken=" + encodeURIComponent(this.csrfToken) + "&snippetname=" + encodeURIComponent(tracySnippetName) + "&snippetcode=" + encodeURIComponent(JSON.stringify(code)));
                    }
                } catch (err) {
                    console.warn('Error modifying snippet:', err);
                }
            },

            // Patch a single item in the snippet list DOM instead of rebuilding the entire list.
            patchSnippetList: function(name, record, isDelete) {
                const ul = document.getElementById('snippetsList');
                if (!ul) {
                    // First render — fall back to full build.
                    this.rebuildSnippetList();
                    return;
                }

                if (isDelete) {
                    const id = this.makeIdFromTitle(name);
                    const li = document.getElementById(id);
                    if (li) li.remove();
                    return;
                }

                // Add or update
                const existingId = this.makeIdFromTitle(name);
                let li = document.getElementById(existingId);
                if (li) {
                    // Update modified timestamp
                    li.dataset.modified = String(record.modified);
                }
                else {
                    li = this._buildSnippetLi(record);
                    ul.appendChild(li);
                }
            },

            // Full rebuild of the snippet list — used only on initial load.
            rebuildSnippetList: async function(existingSnippets) {
                if (!existingSnippets) {
                    existingSnippets = await this.getAllSnippets();
                }
                const tracySnippets = document.getElementById("tracySnippets");
                if (!tracySnippets) {
                    console.warn('Element #tracySnippets not found');
                    return;
                }

                // Build list using DOM APIs — no string interpolation of user data into HTML.
                const ul = document.createElement('ul');
                ul.id = 'snippetsList';

                for (let idx = 0; idx < existingSnippets.length; idx++) {
                    const obj = existingSnippets[idx];
                    if (!obj || typeof obj !== 'object' || !obj.name) continue;
                    ul.appendChild(this._buildSnippetLi(obj));
                }

                // Replace previous content and (re)attach a single delegated listener.
                tracySnippets.innerHTML = '';
                tracySnippets.appendChild(ul);

                // Remove any previous delegated listener before adding a new one.
                if (tracySnippets._delegatedHandler) {
                    tracySnippets.removeEventListener('click', tracySnippets._delegatedHandler);
                }
                tracySnippets._delegatedHandler = function(e) {
                    const deleteTarget = e.target.closest('.consoleSnippetDelete');
                    const loadTarget   = e.target.closest('.consoleSnippetLoad');
                    if (deleteTarget) {
                        const sName = deleteTarget.dataset.snippetName;
                        if (sName) tracyConsole.modifyConsoleSnippets(sName, null, true);
                    } else if (loadTarget) {
                        const sName = loadTarget.dataset.snippetName;
                        if (sName) tracyConsole.loadSnippet(sName);
                    }
                };
                tracySnippets.addEventListener('click', tracySnippets._delegatedHandler);
            },

            _buildSnippetLi: function(obj) {
                const li = document.createElement('li');
                li.title = 'Load in console';
                li.id = this.makeIdFromTitle(obj.name);
                li.dataset.modified = String(obj.modified);
                li.dataset.snippetName = obj.name;

                // Edit icon
                const editSpan = document.createElement('span');
                editSpan.classList.add('consoleSnippetIcon', 'consoleEditIcon');
                editSpan.style.fontFamily = 'FontAwesome';
                const safeEditorLink = this.externalEditorLink.replace('ExternalEditorDummyFile', encodeURIComponent(obj.name));
                editSpan.innerHTML = safeEditorLink;
                li.appendChild(editSpan);

                // Delete icon — uses data-snippet-name, wired via delegation
                const deleteSpan = document.createElement('span');
                deleteSpan.classList.add('consoleSnippetIcon', 'consoleSnippetDelete');
                deleteSpan.style.fontFamily = 'FontAwesome';
                deleteSpan.title = 'Delete snippet';
                deleteSpan.dataset.snippetName = obj.name;
                deleteSpan.innerHTML = '&#xf1f8;';
                li.appendChild(deleteSpan);

                // Name label — uses data-snippet-name, wired via delegation
                const nameSpan = document.createElement('span');
                nameSpan.classList.add('consoleSnippetLoad');
                nameSpan.style.cssText = 'color: #125EAE; cursor: pointer; width:225px; word-break: break-all;';
                nameSpan.dataset.snippetName = obj.name;
                nameSpan.textContent = obj.name;
                li.appendChild(nameSpan);

                return li;
            },

            makeIdFromTitle: function(title) {
                return String(title).replace(/^[^a-z]+|[^\w:.-]+/gi, "");
            },

            saveSnippet: function() {
                const tracySnippetName = document.getElementById("tracySnippetName")?.value || '';
                if (tracySnippetName != "") {
                    this.modifyConsoleSnippets(tracySnippetName, this.tce.getValue());
                    this.disableButton("saveSnippet");
                    this.tce.focus();
                    const tabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label');
                    if (tabButton) {
                        tabButton.textContent = tracySnippetName;
                    }
                    const unsavedChangesIndicator = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .unsaved-changes-indicator');
                    if (unsavedChangesIndicator) {
                        unsavedChangesIndicator.classList.remove('visible');
                    }
                    this.saveToIndexedDB();
                }
                else {
                    alert('You must enter a name to save a snippet!');
                    const snippetNameInput = document.getElementById("tracySnippetName");
                    if (snippetNameInput) snippetNameInput.focus();
                }
            },

            setActiveSnippet: function(name) {
                const item = document.getElementById(this.makeIdFromTitle(name));
                if (!item) return;
                if (document.querySelector(".activeSnippet")) {
                    document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                }
                item.classList.add("activeSnippet");
            },

            compareAlphabetical: function(a1, a2) {
                const t1 = a1.innerText.toLowerCase(),
                    t2 = a2.innerText.toLowerCase();
                return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
            },

            compareChronological: function(a1, a2) {
                const t1 = a1.dataset.modified,
                    t2 = a2.dataset.modified;
                return t1 > t2 ? 1 : (t1 < t2 ? -1 : 0);
            },

            sortUnorderedList: function(ul, sortDescending, type) {
                if (typeof ul == "string") {
                    ul = document.getElementById(ul);
                }
                if (!ul) return;

                const lis = ul.getElementsByTagName("LI");
                const vals = [];

                for (let i = 0, l = lis.length; i < l; i++) {
                    vals.push(lis[i]);
                }

                if (type === 'alphabetical') {
                    vals.sort(this.compareAlphabetical);
                }
                else {
                    vals.sort(this.compareChronological);
                }

                if (sortDescending) {
                    vals.reverse();
                }

                ul.innerHTML = '';
                for (let i = 0, l = vals.length; i < l; i++) {
                    ul.appendChild(vals[i]);
                }
            },

            sortList: function(type) {
                this.sortUnorderedList("snippetsList", this.desc, type);
                this.desc = !this.desc;
                return false;
            },

            getTabItem: async function(id) {
                id = Number(id);
                return this.dbGet('tabs', id);
            },

            // Returns all real tab records (excludes the 'selectedTab' sentinel).
            getAllTabs: async function() {
                return this.dbGetAll('tabs', 'selectedTab');
            },

            reloadSnippet: async function(process = false) {
                try {
                    const result = await this.dbGet('snippets', 'selectedSnippet');
                    const snippetName = result?.value;
                    if (snippetName) {
                        this.loadSnippet(snippetName, process, true, true);
                    }
                } catch (err) {
                    console.warn('Error reloading snippet:', err);
                }
            },

            loadSnippet: async function(name, process = false, get = true, reload = false) {
                try {
                    const existingTabs = await this.getAllTabs();
                    let existingTabId = null;

                    if (get) {
                        for (let i = 0; i < existingTabs.length; i++) {
                            if (existingTabs[i].name === name) {
                                existingTabId = existingTabs[i].id;
                                break;
                            }
                        }
                    }

                    if (existingTabId && !reload) {
                        this.switchTab(existingTabId);
                    }
                    else {
                        let shouldReplaceCurrentTab = false;
                        if (!reload && existingTabs.length === 1) {
                            const currentTab = await this.getTabItem(this.currentTabId);
                            const tabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label');
                            const tabName = tabButton ? tabButton.textContent : '';
                            const isUntitled = tabName.match(/^Untitled-\d+$/);
                            const hasNoCode = !currentTab || !currentTab.code || currentTab.code.trim() === '';
                            shouldReplaceCurrentTab = isUntitled && hasNoCode;
                        }

                        if (get) {
                            if (!reload && !shouldReplaceCurrentTab) {
                                this.loadingSnippet = true;
                                this.addNewTab(name);
                            }
                            else if (shouldReplaceCurrentTab) {
                                this.loadingSnippet = true;
                                const tabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label');
                                if (tabButton) {
                                    tabButton.textContent = name;
                                }
                            }
                            this.lockTabName();
                            await this.getSnippet(name, process);
                            this.loadingSnippet = false;
                        }

                        this.setActiveSnippet(name);

                        // Persist selected snippet sentinel.
                        await this.dbPut('snippets', { name: 'selectedSnippet', value: name });

                        const snippetNameInput = document.getElementById("tracySnippetName");
                        if (snippetNameInput) snippetNameInput.value = name;

                        if (reload) {
                            const tabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label');
                            if (tabButton) tabButton.textContent = name;
                        }

                        this.enableButton("reloadSnippet");
                        this.disableButton("saveSnippet");
                        // explicitly clear the unsaved-changes indicator on the tab button.
                        const activeTabButton = document.querySelector('button[data-tab-id="' + this.currentTabId + '"]');
                        if (activeTabButton) {
                            activeTabButton.querySelector('.unsaved-changes-indicator')?.classList.remove('visible');
                            activeTabButton.querySelector('.close-button')?.classList.add('visible');
                        }
                        this.resizeAce();
                    }
                } catch (err) {
                    console.warn('Error loading tabs in loadSnippet:', err);
                }
            },

            scrollTabIntoView: function(tabId) {
                const tabElement = document.querySelector('[data-tab-id="'+tabId+'"]');
                if (tabElement) {
                    tabElement.scrollIntoView({
                        behavior: "smooth",
                        block: "nearest",
                        inline: "nearest"
                    });
                }
            },

            lockTabName: function() {
                const tabButton = document.querySelector('button[data-tab-id="'+this.currentTabId+'"] .button-label');
                if (tabButton) {
                    tabButton.classList.add("lockedTab");
                }
            },

            // toggleSaveButton: accepts an optional pre-fetched tab to avoid a
            // redundant read (the caller in the 'change' handler already has the tab).
            toggleSaveButton: async function(prefetchedTab) {
                if (this.loadingSnippet) return;
                const tabButton = document.querySelector('button[data-tab-id="' + this.currentTabId + '"]');
                if (!tabButton) return;

                const unsavedChangesIndicator = tabButton.querySelector('.unsaved-changes-indicator');
                const closeButton = tabButton.querySelector('.close-button');

                const tab = prefetchedTab !== undefined ? prefetchedTab : await this.getTabItem(this.currentTabId);
                const hasChanges = await this.checkIfUnsavedChanges(this.currentTabId, tab?.name, tab);

                if (hasChanges) {
                    this.enableButton("saveSnippet");
                    if (unsavedChangesIndicator) unsavedChangesIndicator.classList.add('visible');
                    if (closeButton) closeButton.classList.remove('visible');
                }
                else {
                    this.disableButton("saveSnippet");
                    if (unsavedChangesIndicator) unsavedChangesIndicator.classList.remove('visible');
                    if (closeButton) closeButton.classList.add('visible');
                }
            },

            saveHistory: async function() {
                const code = this.tce.getValue();
                const selections = this.tce.selection.toJSON();
                if (!code) return;

                try {
                    const consoleTab = await this.getTabItem(this.currentTabId);
                    if (!consoleTab) return;

                    const historyData = consoleTab.historyData || [];
                    let historyCount = consoleTab.historyCount || 0;
                    if (historyData.length >= this.maxHistoryItems) {
                        historyData.shift();
                    }
                    historyData.push({
                        code: code,
                        selections: selections,
                        scrollTop: this.tce.session.getScrollTop(),
                        scrollLeft: this.tce.session.getScrollLeft(),
                        splitSizes: consoleTab.splitSizes || [40, 60]
                    });
                    const historyItem = historyData.length - 1;
                    historyCount = historyData.length;
                    this.disableButton("historyForward");
                    if (historyCount > 1) {
                        this.enableButton("historyBack");
                    }
                    await this.dbPut('tabs', {
                        ...consoleTab,
                        historyData: historyData,
                        historyCount: historyCount,
                        historyItem: historyItem
                    });
                } catch (err) {
                    console.warn('Error saving history:', err);
                }
            },

            loadHistory: async function(direction) {
                try {
                    const consoleTab = await this.getTabItem(this.currentTabId);
                    if (!consoleTab) return false;

                    const historyData = consoleTab.historyData || [];
                    const historyCount = consoleTab.historyCount || 0;
                    let historyItem = consoleTab.historyItem || 0;
                    if (historyCount === 0 || historyData.length === 0) {
                        this.disableButton("historyForward");
                        this.disableButton("historyBack");
                        return false;
                    }
                    if (direction === "back" && historyItem > 0) {
                        historyItem--;
                    }
                    else if (direction === "forward" && historyItem < historyCount - 1) {
                        historyItem++;
                    }
                    if (historyItem <= 0) {
                        this.disableButton("historyBack");
                    }
                    else {
                        this.enableButton("historyBack");
                    }
                    if (historyItem >= historyCount - 1) {
                        this.disableButton("historyForward");
                    }
                    else {
                        this.enableButton("historyForward");
                    }
                    if (direction) {
                        const historyEntry = historyData[historyItem];
                        if (historyEntry) {
                            historyEntry.selections = historyEntry.selections || {};
                            historyEntry.scrollTop = historyEntry.scrollTop || 0;
                            historyEntry.scrollLeft = historyEntry.scrollLeft || 0;
                            historyEntry.splitSizes = (historyItem === (historyCount - 1)) ? (consoleTab.splitSizes || historyEntry.splitSizes || [40, 60]) : (historyEntry.splitSizes || [40, 60]);
                            this.setEditorState(historyEntry);
                        }
                        await this.dbPut('tabs', {
                            ...consoleTab,
                            historyItem: historyItem
                        });
                        return true;
                    }
                    else {
                        return false;
                    }
                } catch (err) {
                    console.warn('Error loading history:', err);
                    return false;
                }
            },

            updateBackupState: function() {
                const dbBackup = document.getElementById("dbBackup");
                const backupFilename = document.getElementById("backupFilename");
                if (dbBackup && backupFilename) {
                    if (!dbBackup.checked) {
                        backupFilename.value = '';
                        backupFilename.style.display = "none";
                        document.cookie = "tracyDbBackup=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Strict";
                        document.cookie = "tracyDbBackupFilename=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Strict";
                    }
                    else {
                        backupFilename.style.display = "inline-block";
                    }
                    this.tce.focus();
                }
            },

            switchTab: async function(tabId) {
                tabId = Number(tabId);
                if (this.tabsContainer) {
                    Array.from(this.tabsContainer.children).forEach(function(tab) {
                        tab.classList.remove("active");
                    });
                }
                this.currentTabId = tabId;
                const newButton = this.tabsContainer && Array.from(this.tabsContainer.children).find(
                    function(button) { return button.dataset.tabId == tabId; }
                );
                if (newButton) {
                    newButton.classList.add("active");
                }
                this.scrollTabIntoView(tabId);

                // Parallelise the two independent reads: tab data + snippet item.
                const tabButton = document.querySelector('button[data-tab-id="'+tabId+'"] .button-label');
                const snippetName = tabButton ? tabButton.textContent : null;

                const [tab, snippet] = await Promise.all([
                    this.getTabItem(tabId),
                    snippetName ? this.getSnippetItem(snippetName) : Promise.resolve(undefined)
                ]);

                if (snippetName && snippet) {
                    this.lockTabName();
                    this.setActiveSnippet(snippetName);
                    this.dbPut('snippets', { name: 'selectedSnippet', value: snippetName })
                        .catch(err => console.warn('Error saving selectedSnippet:', err));
                    const snippetNameInput = document.getElementById("tracySnippetName");
                    if (snippetNameInput) snippetNameInput.value = snippetName;
                    this.enableButton("reloadSnippet");
                }
                else {
                    if (document.querySelector(".activeSnippet")) {
                        document.querySelector(".activeSnippet").classList.remove("activeSnippet");
                        const snippetNameInput = document.getElementById("tracySnippetName");
                        if (snippetNameInput) snippetNameInput.value = '';
                    }
                    this.dbDelete('snippets', 'selectedSnippet')
                        .catch(err => console.warn('Error deleting selectedSnippet:', err));
                    this.disableButton("reloadSnippet");
                }

                if (!await this.loadHistory()) {
                    this.setEditorState(tab);
                }
                const resultsDiv = document.getElementById("tracyConsoleResult");
                if (resultsDiv) {
                    resultsDiv.innerHTML = tab && tab.result ? tab.result : '';
                }
                this.setSelectedTabId(this.currentTabId);
            },

            removeTab: async function(tabId) {
                try {
                    const tabButton = this.tabsContainer && Array.from(this.tabsContainer.children)
                        .find(function(button) { return button.dataset.tabId == tabId; });
                    if (tabButton) {
                        this.tabsContainer.removeChild(tabButton);
                    }
                    await this.dbDelete('tabs', tabId);

                    const updatedTabs = await this.getAllTabs();
                    const lastTab = updatedTabs.length > 0 ? updatedTabs[updatedTabs.length - 1] : null;
                    this.currentTabId = lastTab ? lastTab.id : null;
                    this.setSelectedTabId(this.currentTabId);

                    if (updatedTabs.length === 0) {
                        this.addNewTab();
                    } else {
                        this.switchTab(this.currentTabId);
                    }
                } catch (err) {
                    console.warn('Error removing tab:', err);
                }
            },

            addNewTab: async function(name) {
                try {
                    const existingTabs = await this.getAllTabs();
                    const tabId = existingTabs.length ? Math.max.apply(null, existingTabs.map(tab => tab.id)) + 1 : 1;
                    if (!name) {
                        name = 'Untitled-' + this.getNextTabNumber();
                    }
                    this.buildTab(tabId, name);
                    const resultsDiv = document.getElementById("tracyConsoleResult");
                    if (resultsDiv) resultsDiv.innerHTML = '';

                    // Persist the new tab skeleton to DB before switching to it.
                    const tabElements = Array.from(document.querySelectorAll('#tracyTabs button[data-tab-id]'));
                    const currentIndex = tabElements.findIndex(el => parseInt(el.dataset.tabId) === tabId);
                    const newTab = {
                        id: tabId,
                        name: name,
                        code: '',
                        historyData: [],
                        historyItem: null,
                        historyCount: 0,
                        result: '',
                        selections: {},
                        scrollTop: 0,
                        scrollLeft: 0,
                        splitSizes: [40, 60],
                        order: currentIndex >= 0 ? currentIndex : existingTabs.length
                    };
                    await this.dbPut('tabs', newTab);
                    await this.switchTab(tabId);

                    const sizes = await this.getSplits();
                    if (this.split && typeof this.split.getSizes === 'function') {
                        this.split.setSizes(sizes);
                        this.saveSplits();
                    }
                    else {
                        console.warn('Split.js not initialized yet in addNewTab');
                    }
                } catch (err) {
                    console.warn('Error adding new tab:', err);
                }
            },

            buildTab: function(tabId, name) {
                const tabButton = document.createElement("button");
                const buttonLabel = document.createElement("span");
                buttonLabel.classList.add("button-label");
                buttonLabel.textContent = name;
                tabButton.appendChild(buttonLabel);
                tabButton.dataset.tabId = tabId;
                tabButton.setAttribute("draggable", "true");
                const clickHandler = () => tracyConsole.switchTab(tabId);
                tabButton.addEventListener("click", clickHandler);
                listenerMap.set(tabButton, { click: clickHandler });
                if (this.tabsContainer) {
                    this.tabsContainer.appendChild(tabButton);
                }
                const unsavedChangesIndicator = document.createElement("span");
                unsavedChangesIndicator.classList.add("unsaved-changes-indicator");
                unsavedChangesIndicator.textContent = "•";
                tabButton.appendChild(unsavedChangesIndicator);
                const closeButton = document.createElement("span");
                closeButton.classList.add("close-button");
                closeButton.textContent = "×";
                closeButton.title = "Close tab";
                closeButton.classList.add('visible');
                const closeHandler = async function(e) {
                    e.stopPropagation();
                    try {
                        const tab = await tracyConsole.getTabItem(tabId);
                        const hasChanges = await tracyConsole.checkIfUnsavedChanges(tabId, tab?.name, tab);
                        if (hasChanges) {
                            if (confirm("There are unsaved changes, are you sure you want to close this tab?")) {
                                tracyConsole.removeTab(tabId);
                            }
                        }
                        else {
                            tracyConsole.removeTab(tabId);
                        }
                    } catch (err) {
                        console.warn('Error checking tab for close:', err);
                    }
                };
                closeButton.addEventListener("click", closeHandler);
                listenerMap.set(closeButton, { click: closeHandler });
                tabButton.appendChild(closeButton);
            },

            // checkIfUnsavedChanges: accepts an optional pre-fetched tab and/or snippet
            // to avoid redundant DB reads when callers already have those objects.
            checkIfUnsavedChanges: function(tabId, name, prefetchedTab, prefetchedSnippet) {
                return new Promise(resolve => {
                    const withTab = (tab) => {
                        if (!tab) { resolve(false); return; }
                        if (!name) name = tab.name || '';

                        const withSnippet = (snippet) => {
                            const tabCode = (tab.code || '').replace(/\\s+/g, ' ').trim();
                            const snippetCode = (snippet?.code || '').replace(/\\s+/g, ' ').trim();
                            const hasUnsavedChanges = (!snippet && tabCode !== '') || (snippet && tabCode !== snippetCode);
                            resolve(hasUnsavedChanges);
                        };

                        if (prefetchedSnippet !== undefined) {
                            withSnippet(prefetchedSnippet);
                        } else {
                            this.getSnippetItem(name).then(withSnippet).catch(err => {
                                console.warn('Error fetching snippet in checkIfUnsavedChanges:', err);
                                resolve(false);
                            });
                        }
                    };

                    if (prefetchedTab !== undefined) {
                        withTab(prefetchedTab);
                    } else {
                        this.getTabItem(tabId).then(withTab).catch(err => {
                            console.warn('Error fetching tab in checkIfUnsavedChanges:', err);
                            resolve(false);
                        });
                    }
                });
            },

            getNextTabNumber: function() {
                const existingLabels = Array.from(document.querySelectorAll('.button-label')).map(span => span.textContent.trim());
                const untitledPattern = /^Untitled-(\d+)$/;
                let maxNumber = 0;
                existingLabels.forEach(function(label) {
                    const match = label.match(untitledPattern);
                    if (match) {
                        const number = parseInt(match[1], 10);
                        if (number > maxNumber) {
                            maxNumber = number;
                        }
                    }
                });
                return maxNumber + 1;
            },

            getDragAfterElement: function(container, x) {
                const draggableElements = [...container.querySelectorAll("button[draggable='true']:not(.dragging)")];
                return draggableElements.reduce(function(closest, child) {
                    const box = child.getBoundingClientRect();
                    const offset = x - box.left - box.width / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    }
                    else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            },

            // updateTabOrder: update only the 'order' field on each tab record
            // that has actually changed position.
            updateTabOrder: async function() {
                const tabElements = document.querySelectorAll('#tracyTabs button[data-tab-id]');
                const newTabOrder = Array.from(tabElements).map(tab => parseInt(tab.getAttribute('data-tab-id'), 10));

                try {
                    const tabs = await this.getAllTabs();
                    // Build a map for O(1) lookup, then write only changed order fields.
                    const tabMap = new Map(tabs.map(t => [t.id, t]));
                    const updates = [];
                    newTabOrder.forEach(function(tabId, index) {
                        const tab = tabMap.get(tabId);
                        if (tab && tab.order !== index) {
                            updates.push({ ...tab, order: index });
                        }
                    });
                    if (updates.length > 0) {
                        await this.dbPut('tabs', updates);
                    }
                } catch (err) {
                    console.warn('Error updating tab order:', err);
                }
            },

            cleanup: function() {

                if (this.scrollSaveTimer) {
                    clearTimeout(this.scrollSaveTimer);
                    this.scrollSaveTimer = null;
                }
                if (this.saveTimeout) {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = null;
                }

                listenerMap.forEach(function(handlers, element) {
                    if (element instanceof XMLHttpRequest) {
                        if (element.readyState !== XMLHttpRequest.DONE) {
                            element.abort();
                        }
                        if (handlers.onreadystatechange) {
                            element.onreadystatechange = null;
                        }
                        listenerMap.delete(element);
                    }
                });

                if (this.observer) {
                    this.observer.disconnect();
                    this.observer = null;
                }

                const windowHandlers = listenerMap.get(window);
                if (windowHandlers) {
                    if (windowHandlers.resize)       window.removeEventListener('resize', windowHandlers.resize);
                    if (windowHandlers.beforeunload) window.removeEventListener('beforeunload', windowHandlers.beforeunload);
                    listenerMap.delete(window);
                }
                window.onresize = null;

                if (this.tabsContainer) {
                    const tabsContainer = this.tabsContainer;
                    const tabsHandlers = listenerMap.get(tabsContainer);
                    if (tabsHandlers) {
                        if (tabsHandlers.dragstart) tabsContainer.removeEventListener('dragstart', tabsHandlers.dragstart);
                        if (tabsHandlers.dragend)   tabsContainer.removeEventListener('dragend',   tabsHandlers.dragend);
                        if (tabsHandlers.dragover)  tabsContainer.removeEventListener('dragover',  tabsHandlers.dragover);
                        listenerMap.delete(tabsContainer);
                    }
                }
                if (this.addTabButton) {
                    const addTabHandler = listenerMap.get(this.addTabButton)?.click;
                    if (addTabHandler) this.addTabButton.removeEventListener('click', addTabHandler);
                    listenerMap.delete(this.addTabButton);
                }
                document.querySelectorAll('#tracyTabs button[data-tab-id]').forEach(function(tabButton) {
                    const clickHandler = listenerMap.get(tabButton)?.click;
                    const closeBtn = tabButton.querySelector('.close-button');
                    const closeHandler = closeBtn ? listenerMap.get(closeBtn)?.click : null;
                    if (clickHandler) tabButton.removeEventListener('click', clickHandler);
                    if (closeHandler && closeBtn) closeBtn.removeEventListener('click', closeHandler);
                    listenerMap.delete(tabButton);
                    if (closeBtn) listenerMap.delete(closeBtn);
                });

                // Remove delegated snippet list handler
                const tracySnippets = document.getElementById("tracySnippets");
                if (tracySnippets && tracySnippets._delegatedHandler) {
                    tracySnippets.removeEventListener('click', tracySnippets._delegatedHandler);
                    tracySnippets._delegatedHandler = null;
                }

                if (this.tce) {
                    this.tce.off('beforeEndOperation');
                    this.tce.session.off('changeScrollTop');
                    this.tce.session.off('changeScrollLeft');
                    this.tce.destroy();
                    this.tce = null;
                }
                const codeInput = document.getElementById("tracyConsoleCode")?.querySelector(".ace_text-input");
                if (codeInput) {
                    const keydownHandler = listenerMap.get(codeInput)?.keydown;
                    if (keydownHandler) codeInput.removeEventListener('keydown', keydownHandler);
                    listenerMap.delete(codeInput);
                }
                const snippetNameInput = document.getElementById("tracySnippetName");
                if (snippetNameInput) {
                    snippetNameInput.onkeyup = null;
                }
                const consolePanel = document.getElementById("tracy-debug-panel-ProcessWire-ConsolePanel");
                if (consolePanel && this.isSafari()) {
                    const mousemoveHandler = listenerMap.get(consolePanel)?.mousemove;
                    if (mousemoveHandler) consolePanel.removeEventListener('mousemove', mousemoveHandler);
                    listenerMap.delete(consolePanel);
                }
                if (db) {
                    db.close();
                    db = null;
                    dbReady = false;
                }
            }
        };

        const loadSplitJs = () => new Promise(resolve => {
            tracyJSLoader.load(tracyConsole.tracyModuleUrl + "/scripts/splitjs/split.min.js", function() {
                resolve();
            });
        });

        tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/ace-editor/ace.js", function() {
            if (typeof ace !== "undefined") {
                tracyConsole.tce = ace.edit("tracyConsoleEditor");
                tracyConsole.tce.container.style.lineHeight = tracyConsole.lineHeight + 'px';
                tracyConsole.tce.setFontSize(tracyConsole.codeFontSize);
                tracyConsole.tce.setShowPrintMargin(false);
                tracyConsole.tce.setShowInvisibles($codeShowInvisibles);
                tracyConsole.tce.\$blockScrolling = Infinity;

                const initializeTabs = async function() {
                    tracyConsole.tabsWrapper = document.getElementById('tracyTabsWrapper');
                    tracyConsole.tabsContainer = document.getElementById("tracyTabs");

                    if (tracyConsole.tabsWrapper && tracyConsole.tabsContainer) {
                        const resizeObserver = new ResizeObserver(function() {
                            if (tracyConsole.tabsContainer.scrollWidth <= tracyConsole.tabsWrapper.clientWidth) {
                                tracyConsole.tabsWrapper.style.overflowX = 'hidden';
                            }
                            else {
                                tracyConsole.tabsWrapper.style.overflowX = 'auto';
                            }
                        });
                        resizeObserver.observe(tracyConsole.tabsWrapper);
                        resizeObserver.observe(tracyConsole.tabsContainer);
                    }

                    tracyConsole.addTabButton = document.getElementById("addTab");
                    if (tracyConsole.addTabButton) {
                        const addTabHandler = () => tracyConsole.addNewTab();
                        tracyConsole.addTabButton.addEventListener("click", addTabHandler);
                        listenerMap.set(tracyConsole.addTabButton, { click: addTabHandler });
                    }
                    let draggedTab = null;
                    if (tracyConsole.tabsContainer) {
                        const dragStartHandler = function(e) {
                            if (e.target === tracyConsole.addTabButton) return;
                            draggedTab = e.target;
                            e.dataTransfer.effectAllowed = "move";
                            e.target.classList.add("dragging");
                        };
                        const dragEndHandler = function(e) {
                            e.target.classList.remove("dragging");
                            draggedTab = null;
                            tracyConsole.updateTabOrder();
                        };
                        const dragOverHandler = function(e) {
                            e.preventDefault();
                            const afterElement = tracyConsole.getDragAfterElement(tracyConsole.tabsContainer, e.clientX);
                            if (!tracyConsole.tabsContainer.contains(draggedTab)) {
                                console.error("Dragged tab is not a child of tracyTabs.");
                                return;
                            }
                            if (afterElement === tracyConsole.addTabButton || afterElement == null) {
                                tracyConsole.tabsContainer.appendChild(draggedTab);
                            }
                            else {
                                if (tracyConsole.tabsContainer.contains(afterElement)) {
                                    tracyConsole.tabsContainer.insertBefore(draggedTab, afterElement);
                                }
                                else {
                                    console.error("afterElement is not a child of tracyTabs.");
                                }
                            }
                        };
                        tracyConsole.tabsContainer.addEventListener("dragstart", dragStartHandler);
                        tracyConsole.tabsContainer.addEventListener("dragend", dragEndHandler);
                        tracyConsole.tabsContainer.addEventListener("dragover", dragOverHandler);
                        listenerMap.set(tracyConsole.tabsContainer, {
                            dragstart: dragStartHandler,
                            dragend: dragEndHandler,
                            dragover: dragOverHandler
                        });
                    }

                    try {
                        await dbReadyPromise;
                        await tracyConsole.migrateLocalStorageToIndexedDB();

                        const selectedTabId = tracyConsole.getSelectedTabId();

                        // Single transaction: load all tabs + seed server snippets.
                        const tx = db.transaction(['tabs', 'snippets'], 'readwrite');
                        const tabStore = tx.objectStore('tabs');
                        const snippetStore = tx.objectStore('snippets');

                        // Seed server-side snippets.
                        const serverSnippets = $snippets;
                        serverSnippets.forEach(s => snippetStore.put(s));

                        // Load all tabs in one pass.
                        const tabsLoaded = new Promise(function(resolve) {
                            tabStore.getAll().onsuccess = function(event) {
                                const allRecords = event.target.result || [];
                                const existingTabs = allRecords.filter(item => item.id !== 'selectedTab');
                                resolve(existingTabs);
                            };
                        });

                        tx.oncomplete = async function() {
                            const existingTabs = await tabsLoaded;

                            tracyConsole.currentTabId = Number(selectedTabId);

                            if (existingTabs && existingTabs.length > 0) {
                                existingTabs.sort(function(a, b) {
                                    const orderA = a.order !== undefined ? a.order : a.id;
                                    const orderB = b.order !== undefined ? b.order : b.id;
                                    return orderA - orderB;
                                });
                                existingTabs.forEach(function(consoleTab) {
                                    if (!consoleTab.name || !consoleTab.name.trim().length) {
                                        consoleTab.name = 'Untitled-' + tracyConsole.getNextTabNumber();
                                    }
                                    tracyConsole.buildTab(consoleTab.id, consoleTab.name);
                                });
                                tracyConsole.switchTab(tracyConsole.currentTabId);
                            }
                            else {
                                tracyConsole.addNewTab();
                            }

                            // Load snippets list.
                            const allSnippets = await tracyConsole.getAllSnippets();
                            tracyConsole.rebuildSnippetList(allSnippets);

                            try {
                                const selectedSnippetRecord = await tracyConsole.dbGet('snippets', 'selectedSnippet');
                                const selectedSnippet = selectedSnippetRecord?.value;
                                if (selectedSnippet) {
                                    const tracyConsoleSelectedSnippetEl = document.getElementById(tracyConsole.makeIdFromTitle(selectedSnippet));
                                    if (tracyConsoleSelectedSnippetEl) {
                                        document.getElementById("tracySnippetName").value = escapeHtml(selectedSnippet);
                                        tracyConsoleSelectedSnippetEl.classList.add("activeSnippet");
                                        tracyConsole.enableButton("reloadSnippet");
                                    }
                                }
                            } catch (err) {
                                console.warn('Error loading selected snippet on init:', err);
                            }
                        };

                        tx.onerror = function(err) {
                            console.warn('Error during init transaction:', err);
                        };

                    } catch (err) {
                        console.warn('Database initialization failed, creating default tab:', err);
                        tracyConsole.currentTabId = 1;
                        tracyConsole.addNewTab();
                    }
                    tracyConsole.sortList('alphabetical');
                };

                loadSplitJs().then(function() {
                    initializeTabs();
                }).catch(err => console.warn('Error loading Split.js:', err));

                tracyConsole.tce.on("beforeEndOperation", function() {
                    const tabButton = document.querySelector('button[data-tab-id="' + tracyConsole.currentTabId + '"]');
                    const label = tabButton?.querySelector('.button-label');
                    let potentialTabName = tracyConsole.tce.session.getLine(0).substring(0, 20).trim();
                    let tabName = potentialTabName || (!label || label.textContent.length <= 1 ? 'Untitled-' + tracyConsole.getNextTabNumber() : label.textContent);
                    if (label && !label.classList.contains('lockedTab') && tabName !== label.textContent) {
                        label.textContent = tabName;
                    }
                    clearTimeout(tracyConsole.saveTimeout);
                    tracyConsole.saveTimeout = setTimeout(async function() {
                        try {
                            // Save and pass the result to toggleSaveButton to avoid double-read.
                            await tracyConsole.saveToIndexedDB();
                            const code = tracyConsole.tce.getValue();
                            const mode = code.includes('<?php') ? 'ace/mode/php' : { path: 'ace/mode/php', inline: true };
                            tracyConsole.tce.session.setMode(mode);
                            tracyConsole.toggleSaveButton();
                            tracyConsole.resizeAce(false);
                        } catch (err) {
                            console.warn('Error saving tab:', err);
                        }
                    }, 300);
                });

                tracyConsole.tce.session.on("changeScrollTop", function() {
                    tracyConsole.scheduleSave();
                });

                tracyConsole.tce.session.on("changeScrollLeft", function() {
                    tracyConsole.scheduleSave();
                });

                tracyConsole.tce.setTheme("ace/theme/" + tracyConsole.aceTheme);

                ace.config.loadModule('ace/ext/language_tools', function() {
                    tracyConsole.tce.setOptions({
                        enableBasicAutocompletion: true,
                        enableSnippets: true,
                        enableLiveAutocompletion: true,
                        tabSize: $codeTabSize,
                        useSoftTabs: $codeUseSoftTabs,
                        minLines: 5
                    });

                    if (tracyConsole.pwAutocomplete.length > 0) {
                        const staticWordCompleter = {
                            getCompletions: function(editor, session, pos, prefix, callback) {
                                callback(null, tracyConsole.pwAutocomplete.map(function(word) {
                                    return {
                                        value: word.name,
                                        meta: word.meta,
                                        docHTML: word.docHTML
                                    };
                                }));
                            }
                        };
                        tracyConsole.tce.completers.push(staticWordCompleter);
                    }

                    tracyConsole.snippetManager = ace.require("ace/snippets").snippetManager;
                    tracyJSLoader.load(tracyConsole.tracyModuleUrl + "scripts/code-snippets.js", function() {
                        tracyConsole.snippetManager.register(getCodeSnippets(), "php-inline");
                        if (tracyConsole.customSnippetsUrl !== '') {
                            tracyJSLoader.load(tracyConsole.customSnippetsUrl, function() {
                                tracyConsole.snippetManager.register(getCustomCodeSnippets(), "php-inline");
                            });
                        }
                    });

                    tracyConsole.tce.commands.addCommands([
                        {
                            name: "increaseFontSize",
                            bindKey: "Ctrl-=|Ctrl-+",
                            exec: function(editor) {
                                const size = parseInt(tracyConsole.tce.getFontSize(), 10) || 12;
                                editor.setFontSize(size + 1);
                            }
                        },
                        {
                            name: "decreaseFontSize",
                            bindKey: "Ctrl+-|Ctrl-_",
                            exec: function(editor) {
                                const size = parseInt(editor.getFontSize(), 10) || 12;
                                editor.setFontSize(Math.max(size - 1 || 1));
                            }
                        },
                        {
                            name: "resetFontSize",
                            bindKey: "Ctrl+0|Ctrl-Numpad0",
                            exec: function(editor) {
                                editor.setFontSize(14);
                            }
                        }
                    ]);

                    tracyConsole.tce.setAutoScrollEditorIntoView(true);
                    tracyConsole.resizeAce();

                    const toggleFullscreenButton = document.createElement('div');
                    toggleFullscreenButton.innerHTML = '<span class="fullscreenToggleButton" title="Toggle fullscreen" onclick="tracyConsole.toggleFullscreen()">$maximizeSvg</span>';
                    const aceGutter = document.getElementById("tracyConsoleContainer").querySelector('.ace_gutter');
                    if (aceGutter) aceGutter.prepend(toggleFullscreenButton);

                    const codeInput = document.getElementById("tracyConsoleCode").querySelector(".ace_text-input");
                    if (codeInput) {
                        const keydownHandler = function(e) {
                            if (document.getElementById("tracy-debug-panel-ProcessWire-ConsolePanel").classList.contains("tracy-focused")) {

                                // --- Existing: Ctrl/Meta/Alt + Enter (no shift) → run code ---
                                if (((e.keyCode == 10 || e.charCode == 10) || (e.keyCode == 13 || e.charCode == 13)) && (e.metaKey || e.ctrlKey || e.altKey) && !e.shiftKey) {
                                    e.preventDefault();
                                    if (e.altKey) tracyConsole.clearResults();
                                    if ((e.metaKey || e.ctrlKey) && e.altKey) {
                                        tracyConsole.reloadAndRun();
                                    } else {
                                        tracyConsole.processTracyCode();
                                    }
                                }

                                // --- Existing: Alt + PageUp/PageDown → history ---
                                if ((e.keyCode == 33 || e.charCode == 33) && e.altKey) {
                                    tracyConsole.loadHistory('back');
                                }
                                if ((e.keyCode == 34 || e.charCode == 34) && e.altKey) {
                                    tracyConsole.loadHistory('forward');
                                }

                                // --- From pasted code: Shift + Enter/Backspace → auto-resize code pane ---
                                if (e.shiftKey && !e.ctrlKey && !e.metaKey && ((e.keyCode == 13 || e.charCode == 13) || (e.keyCode == 8 || e.charCode == 8))) {
                                    var numLines = tracyConsole.tce.session.getLength();
                                    if (e.keyCode == 13 || e.charCode == 13) numLines++;
                                    var containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                    var collapsedCodePaneHeightPct = (tracyConsole.lineHeight + (tracyConsole.consoleGutterSize / 2)) / containerHeight * 100;
                                    var codeLinesHeight = (numLines * tracyConsole.lineHeight + (tracyConsole.consoleGutterSize / 2));
                                    var codeLinesHeightPct = codeLinesHeight / containerHeight * 100;
                                    if (containerHeight - codeLinesHeight < tracyConsole.lineHeight) codeLinesHeightPct = 100 - collapsedCodePaneHeightPct;
                                    tracyConsole.split.setSizes([codeLinesHeightPct, 100 - codeLinesHeightPct]);
                                    tracyConsole.saveSplits();
                                }

                                // --- From pasted code: Ctrl + Shift combos → pane manipulation ---
                                if (e.ctrlKey && e.shiftKey) {
                                    e.preventDefault();
                                    var containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                    var collapsedCodePaneHeightPct = (tracyConsole.lineHeight + (tracyConsole.consoleGutterSize / 2)) / containerHeight * 100;

                                    // Ctrl+Shift+Enter → toggle fullscreen
                                    if ((e.keyCode == 10 || e.charCode == 10) || (e.keyCode == 13 || e.charCode == 13)) {
                                        tracyConsole.toggleFullscreen();
                                    }
                                    // Ctrl+Shift+Down → maximize code pane
                                    if (e.keyCode == 40 || e.charCode == 40) {
                                        tracyConsole.split.collapse(1);
                                    }
                                    // Ctrl+Shift+Up → minimize code pane
                                    if (e.keyCode == 38 || e.charCode == 38) {
                                        tracyConsole.split.collapse(0);
                                    }
                                    // Ctrl+Shift+PageDown → add row to code pane
                                    if (e.keyCode == 34 || e.charCode == 34) {
                                        var sizes = tracyConsole.split.getSizes();
                                        if (sizes[1] > collapsedCodePaneHeightPct + tracyConsole.consoleGutterSize) {
                                            var codePaneHeight = (sizes[0] / 100 * containerHeight) - (tracyConsole.consoleGutterSize / 2);
                                            codePaneHeight = Math.round(codePaneHeight + tracyConsole.lineHeight);
                                            codePaneHeight = Math.ceil(codePaneHeight / tracyConsole.lineHeight) * tracyConsole.lineHeight;
                                            sizes[0] = codePaneHeight / containerHeight * 100;
                                            sizes[1] = 100 - sizes[0];
                                            tracyConsole.split.setSizes(sizes);
                                        } else {
                                            tracyConsole.split.collapse(1);
                                        }
                                        tracyConsole.saveSplits();
                                    }
                                    // Ctrl+Shift+PageUp → remove row from code pane
                                    if (e.keyCode == 33 || e.charCode == 33) {
                                        var sizes = tracyConsole.split.getSizes();
                                        if (sizes[0] > collapsedCodePaneHeightPct + tracyConsole.consoleGutterSize) {
                                            var codePaneHeight = (sizes[0] / 100 * containerHeight) - (tracyConsole.consoleGutterSize / 2);
                                            codePaneHeight = Math.round(codePaneHeight - tracyConsole.lineHeight);
                                            codePaneHeight = Math.ceil(codePaneHeight / tracyConsole.lineHeight) * tracyConsole.lineHeight;
                                            sizes[0] = codePaneHeight / containerHeight * 100;
                                            sizes[1] = 100 - sizes[0];
                                            tracyConsole.split.setSizes(sizes);
                                        } else {
                                            tracyConsole.split.collapse(0);
                                        }
                                        tracyConsole.saveSplits();
                                    }
                                    // Ctrl+Shift+Right → expand to fit all code
                                    if (e.keyCode == 39 || e.charCode == 39) {
                                        var codeLinesHeight = (tracyConsole.tce.session.getLength() * tracyConsole.lineHeight + (tracyConsole.consoleGutterSize / 2));
                                        var codeLinesHeightPct = codeLinesHeight / containerHeight * 100;
                                        if (containerHeight - codeLinesHeight < tracyConsole.lineHeight) codeLinesHeightPct = 100 - collapsedCodePaneHeightPct;
                                        tracyConsole.split.setSizes([codeLinesHeightPct, 100 - codeLinesHeightPct]);
                                    }
                                    // Ctrl+Shift+Left → restore saved split position
                                    if (e.keyCode == 37 || e.charCode == 37) {
                                        var sizes = tracyConsole.getSplits();
                                        sizes = sizes ? sizes : [40, 60];
                                        tracyConsole.split.setSizes(sizes);
                                    }
                                }
                            }
                            tracyConsole.resizeAce();
                        };
                        codeInput.addEventListener("keydown", keydownHandler);
                        listenerMap.set(codeInput, { keydown: keydownHandler });
                    }

                    const snippetNameInput = document.getElementById("tracySnippetName");
                    if (snippetNameInput) {
                        const keyupHandler = function() {
                            tracyConsole.enableButton("saveSnippet");
                        };
                        snippetNameInput.onkeyup = keyupHandler;
                        listenerMap.set(snippetNameInput, { keyup: keyupHandler });
                    }

                    for (let i = 0; i < document.styleSheets.length; i++) {
                        const style = document.styleSheets[i];
                        style.ownerNode.classList.add("tracy-debug");
                    }
                });

                const elements = document.getElementsByClassName('gutter');
                while (elements.length > 1) {
                    elements[0].parentNode.removeChild(elements[0]);
                }
                tracyConsole.resizeAce();

                const config = { attributes: true, attributeOldValue: true };
                tracyConsole.observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (tracyConsole.split) {
                            (async function() {
                                const containerHeight = document.getElementById('tracyConsoleContainer').offsetHeight;
                                let sizes = tracyConsole.split.getSizes();
                                if (sizes[0] < 0 || sizes[1] < 0) {
                                    sizes = await tracyConsole.getSplits();
                                    sizes = sizes || [40, 60];
                                }
                                if (sizes[0] * containerHeight / 100 < tracyConsole.minSize - (tracyConsole.consoleGutterSize / 2)) {
                                    tracyConsole.split.collapse(0);
                                }
                                else if (sizes[1] * containerHeight / 100 < tracyConsole.minSize - (tracyConsole.consoleGutterSize / 2)) {
                                    tracyConsole.split.collapse(1);
                                }
                                else {
                                    tracyConsole.split.setSizes(sizes);
                                }
                            })();
                        }
                        if (
                            mutation.attributeName == 'class' &&
                            mutation.oldValue !== mutation.target.className &&
                            mutation.oldValue.indexOf('tracy-focused') === -1 &&
                            mutation.target.classList.contains('tracy-focused')
                        ) {
                            tracyConsole.resizeAce();
                        }
                        else if (mutation.attributeName == 'style') {
                            tracyConsole.resizeAce(false);
                        }
                    });
                });
                const consolePanel = document.getElementById("tracy-debug-panel-ProcessWire-ConsolePanel");
                if (consolePanel) {
                    tracyConsole.observer.observe(consolePanel, config);
                    if (tracyConsole.isSafari()) {
                        const mousemoveHandler = function() {
                            tracyConsole.resizeAce();
                        };
                        consolePanel.addEventListener('mousemove', mousemoveHandler);
                        listenerMap.set(consolePanel, { mousemove: mousemoveHandler });
                    }
                }

                const resizeHandler = function(event) {
                    if (document.getElementById("tracy-debug-panel-ProcessWire-ConsolePanel").classList.contains("tracy-focused")) {
                        tracyConsole.resizeAce();
                    }
                };
                window.addEventListener('resize', resizeHandler);
                const existingWindowHandlers = listenerMap.get(window) || {};
                listenerMap.set(window, { ...existingWindowHandlers, resize: resizeHandler });
            }
        });

        function loadFAIfNotAlreadyLoaded() {
            if (!document.getElementById("fontAwesome")) {
                const link = document.createElement("link");
                link.rel = "stylesheet";
                link.href = "/wire/templates-admin/styles/font-awesome/css/font-awesome.min.css";
                document.getElementsByTagName("head")[0].appendChild(link);
            }
        }
        loadFAIfNotAlreadyLoaded();

        const unloadHandler = () => tracyConsole.cleanup();
        window.addEventListener('beforeunload', unloadHandler);
        const currentWindowHandlers = listenerMap.get(window) || {};
        listenerMap.set(window, { ...currentWindowHandlers, beforeunload: unloadHandler });
        </script>

HTML;

        $out .= '
        <h1>' . $this->icon . ' Console
            <span title="Keyboard Shortcuts (toggle on/off)" style="display: inline-block; margin-left: 10px; cursor: pointer" onclick="tracyConsole.toggleKeyboardShortcuts()">⌘</span>
            <span id="tracyConsoleStatus" style="padding-left: 50px"></span>
        </h1>
        <span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'ConsolePanel\')">⛶</a></span></span>
        <div class="tracy-inner">

            <div style="position: relative; height: calc(100% - 80px)">

                <div id="tracyConsoleMainContainer" class="tracy-console-'.TracyDebugger::getDataValue('consoleTabsTheme').'" style="position: absolute; height: 100%; width: '.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '100%' : 'calc(100% - 290px)').'">

                    <div id="consoleKeyboardShortcuts" class="keyboardShortcuts tracyHidden">';
                        $panel = 'console';
                        include($this->wire('config')->paths->TracyDebugger.'includes/AceKeyboardShortcuts.php');
                        $out .= $aceKeyboardShortcuts . '
                    </div>
                    ';

                    $out .= '
                    <div style="margin-bottom: 7px">
                        <span style="display: inline-block; padding: 0 10px 5px 0">
                            <input id="reloadSnippet" title="Reload current snippet from disk" class="disabledButton" style="font-family: FontAwesome !important; padding: 3px 8px !important;" type="submit" onclick="tracyConsole.reloadSnippet()" value="&#xf021" disabled="true" />&nbsp;&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go back (ALT + PageUp)" id="historyBack" class="disabledButton" disabled="true" type="submit" onclick="tracyConsole.loadHistory(\'back\')" value="&#xf060;" />&nbsp;
                            <input style="font-family: FontAwesome !important" title="Go forward (ALT + PageDown)" id="historyForward" class="disabledButton" disabled="true" type="submit" onclick="tracyConsole.loadHistory(\'forward\')" value="&#xf061;" />&nbsp;
                        </span>

                        <span style="display: inline-block; padding: 0 10px 0 0">
                            <label title="Backup entire database before executing script.">
                                <input type="checkbox" id="dbBackup" '.($this->wire('input')->cookie->tracyDbBackup ? 'checked="checked"' : '').' onclick="tracyConsole.updateBackupState();" /> Backup DB
                            </label>&nbsp;&nbsp;
                            <input id="backupFilename" type="text" placeholder="Backup name (optional)" '.($this->wire('input')->cookie->tracyDbBackup ? 'style="display:inline-block !important"' : 'style="display:none !important"').' '.($this->wire('input')->cookie->tracyDbBackupFilename ? 'value="'.htmlspecialchars($this->wire('input')->cookie->tracyDbBackupFilename, ENT_QUOTES, 'UTF-8').'"' : '').' />
                        </span>
                        <span style="display: inline-block; padding: 0 20px 5px 0">
                            <label title="Send full stack trace of errors to Tracy bluescreen">
                                <input type="checkbox" id="allowBluescreen" /> Allow bluescreen
                            </label>
                        </span>
                        ';

                        if(!$inAdmin) {
                            $out .= '
                        <span style="display: inline-block; padding: 0 20px 5px 0">
                            <label title="Access custom variables & functions from this page\'s template file & included files."><input type="checkbox" id="accessTemplateVars" onclick="tracyConsole.tce.focus();" /> Template resources</label>
                        </span>';
                        }

                        $out .= '
                        <span style="display:inline-block; padding-right: 5px;">
                            <input title="Clear results" type="submit" class="clearResults" style="padding: 3px 5px !important" onclick="tracyConsole.clearResults()" value="&#10006; Clear results" />
                            <select name="includeCode" style="height: 25px !important" title="When to execute code" onchange="tracyConsole.tracyIncludeCode(this)" />
                                <option value="off"' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? ' selected' : '') . '>@ Run</option>
                                <option value="init"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'init' ? ' selected' : '') . '>@ Init</option>
                                <option value="ready"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'ready' ? ' selected' : '') . '>@ Ready</option>
                                <option value="finished"' . ($this->tracyIncludeCode && $this->tracyIncludeCode['when'] === 'finished' ? ' selected' : '') . '>@ Finished</option>
                            </select>
                        </span>
                        <input id="runInjectButton" title="&bull; Run (CTRL/CMD + Enter)&#10;&bull; Clear & Run (ALT/OPT + Enter)&#10;&bull; Reload from Disk, Clear & Run&#10;(CTRL/CMD + ALT/OPT + Enter)" type="submit" onclick="tracyConsole.processTracyCode()" value="' . (!$this->tracyIncludeCode || $this->tracyIncludeCode['when'] === 'off' ? 'Run' : 'Inject') . '" />
                        <span id="snippetPaneToggle" title="Toggle snippets pane" style="font-family: FontAwesome !important; position:absolute; top: 0; right: '.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '0' : '-290').'px; font-weight: bold; cursor: pointer" onclick="tracyConsole.toggleSnippetsPane()">'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? '&#xf053;' : '&#xf054;').'</span>
                    </div>

                    <div id="tracyConsoleContainer" class="split" style="height: 100%; min-height: '.$codeLineHeight.'px">
                        <div id="tracyTabsContainer">
                            <div id="tracyTabsWrapper">
                                <div id="tracyTabs"></div>
                            </div>
                            <button id="addTab" title="Add tab" style="font-weight: 600">+</button>
                        </div>
                        <div style="height: calc(100% - 31px)">
                            <div id="tracyConsoleCode" class="split" style="position: relative; background: #FFFFFF;">
                                <div id="tracyConsoleEditor" style="height: 100%; min-height: '.$codeLineHeight.'px"></div>
                            </div>
                            <div id="tracyConsoleResult" class="split" style="position:relative; padding:0 10px; overflow:auto; border:1px solid #D2D2D2;">';

                    if($dbRestoreMessageSafe) {
                        $out .= '<div style="padding: 10px 0">' . $dbRestoreMessageSafe . '</div>' .
                                '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                    }
                    if($tracyCodeErrorSafe) {
                        $out .= '<div style="padding: 10px 0">' . $tracyCodeErrorSafe . '</div>' .
                                '<div style="padding: 10px; border-bottom: 1px dotted #cccccc; padding: 3px; margin:5px 0;"></div>';
                    }
                    $out .= '
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tracySnippetsContainer" style="position: absolute; right:0; margin: 0 0 0 10px; width: 275px; height: calc(100% - 15px);"'.($this->wire('input')->cookie->tracySnippetsPaneCollapsed ? ' class="tracyHidden"' : '').'">
                    <div style="padding-bottom:5px">
                        Sort: <a href="#" onclick="tracyConsole.sortList(\'alphabetical\')">alphabetical</a>&nbsp;|&nbsp;<a href="#" onclick="tracyConsole.sortList(\'chronological\')">chronological</a>
                    </div>
                    <div style="position: relative; width:100% !important;">
                        <input type="text" id="tracySnippetName" placeholder="Enter filename (eg. myscript.php)" />
                        <input id="saveSnippet" type="submit" style="font-family: FontAwesome !important" class="disabledButton" onclick="tracyConsole.saveSnippet()" value="&#xf0c7;" title="Save snippet" />
                    </div>
                    <div id="tracySnippets"></div>
                </div>

            </div>
            ';
        $out .= TracyDebugger::generatePanelFooter('console', Debugger::timer('console'), strlen($out), 'consolePanel');
        $out .= '
        </div>';

        return parent::loadResources() . TracyDebugger::minify($out);

    }

}
