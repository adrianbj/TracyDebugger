(function() {
    var minPollInterval = 3000;
    var maxPollInterval = 30000;
    var pollInterval = minPollInterval;
    var pollTimer = null;
    var currentUrl = window.location.href.split("#")[0];
    var recorderPanelId = "tracy-debug-panel-ProcessWire-DumpsRecorderPanel";
    var lastSeenId = -1;

    function maxId(entries) {
        var m = 0;
        for(var i = 0; i < entries.length; i++) {
            if(entries[i].id > m) m = entries[i].id;
        }
        return m;
    }

    function scheduleNext(hadNewData) {
        if(hadNewData) {
            pollInterval = minPollInterval;
        } else {
            pollInterval = Math.min(pollInterval * 2, maxPollInterval);
        }
        pollTimer = setTimeout(poll, pollInterval);
    }

    function poll() {
        if(document.hidden) {
            pollTimer = setTimeout(poll, pollInterval);
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open("POST", currentUrl, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.timeout = 10000;

        xhr.onreadystatechange = function() {
            if(xhr.readyState !== XMLHttpRequest.DONE) return;
            if(xhr.status !== 200) {
                scheduleNext(false);
                return;
            }
            try {
                var data = JSON.parse(xhr.responseText);
            } catch(e) {
                scheduleNext(false);
                return;
            }
            var entries = data.entries || [];
            var topId = maxId(entries);
            var hadNewData = (lastSeenId === -1) ? entries.length > 0 : topId !== lastSeenId;
            updateRecorderPanel(entries);
            scheduleNext(hadNewData);
        };

        xhr.send("tracyDumpsPoll=1");
    }

    function buildRecorderHtml(entries) {
        var html = "";
        for(var i = 0; i < entries.length; i++) {
            var entry = entries[i];
            var meta = "";
            if(entry.user || entry.time) {
                meta = '<span style="color:#888; font-size:11px; font-weight:normal; margin-left:auto">';
                if(entry.user) meta += escapeHtml(entry.user);
                if(entry.time) meta += ' @ ' + escapeHtml(entry.time);
                meta += '</span>';
            }
            var title = entry.title ? escapeHtml(entry.title) : "&nbsp;";
            if(meta || title) {
                html += '<h2 style="display:flex; align-items:center"><span>' + title + '</span>' + meta + '</h2>';
            }
            html += entry.dump;
        }
        return html;
    }

    function removeNoRecordsText(container) {
        for(var i = container.childNodes.length - 1; i >= 0; i--) {
            var node = container.childNodes[i];
            if(node.nodeType === 3 && node.textContent.indexOf("No Dumps Recorded") !== -1) {
                node.remove();
            }
        }
    }

    function updateRecorderPanel(entries) {
        var panel = document.getElementById(recorderPanelId);
        if(!panel) {
            // panel isn't in the bar on this page — new dumps will appear on the
            // next natural reload; don't force one, to avoid surprising the user
            return;
        }

        var i;
        var topId = maxId(entries);
        var firstSync = (lastSeenId === -1);
        // ids are monotonic, so a drop means the file was cleared and ids restarted
        var wasCleared = !firstSync && topId < lastSeenId;
        var newEntries = (firstSync || wasCleared) ? entries : entries.filter(function(e) { return e.id > lastSeenId; });

        if(panel.dataset && panel.dataset.tracyContent) {
            var html = buildRecorderHtml(entries);
            var temp = document.createElement("div");
            temp.innerHTML = panel.dataset.tracyContent;
            var tempContainer = temp.querySelector("#tracyDumpsRecorderEntries");
            if(tempContainer) {
                removeNoRecordsText(tempContainer);
                var tempItems = tempContainer.querySelector(".dumpsrecorder-items");
                if(!tempItems) {
                    tempItems = document.createElement("div");
                    tempItems.className = "dumpsrecorder-items";
                    tempContainer.appendChild(tempItems);
                }
                tempItems.innerHTML = html;
                // ensure the Clear Dumps button is present in the cached HTML so it
                // appears when the panel is opened (mirrors the DOM-side branch below)
                if(entries.length > 0 && !temp.querySelector("#clearRecorderDumpsBtn")) {
                    var tempBtn = document.createElement("div");
                    tempBtn.style.cssText = "margin:10px 0 5px 0; text-align:right";
                    tempBtn.innerHTML = '<input type="submit" id="clearRecorderDumpsBtn" value="Clear Dumps" />';
                    tempContainer.parentNode.insertBefore(tempBtn, tempContainer.nextSibling);
                }
            }
            panel.dataset.tracyContent = temp.innerHTML;
        } else {
            var container = document.getElementById("tracyDumpsRecorderEntries");
            if(!container) return;

            removeNoRecordsText(container);

            var itemsDiv = container.querySelector(".dumpsrecorder-items");
            if(!itemsDiv) {
                itemsDiv = document.createElement("div");
                itemsDiv.className = "dumpsrecorder-items";
                container.appendChild(itemsDiv);
            }

            if(wasCleared || (firstSync && entries.length > 0)) {
                itemsDiv.innerHTML = buildRecorderHtml(entries);
                if(window.Tracy && Tracy.Dumper) Tracy.Dumper.init(container);
            } else if(!firstSync && newEntries.length > 0) {
                var newHtml = buildRecorderHtml(newEntries);
                itemsDiv.insertAdjacentHTML('beforeend', newHtml);
                if(window.Tracy && Tracy.Dumper) Tracy.Dumper.init(itemsDiv);
            }

            if(!document.getElementById("clearRecorderDumpsBtn")) {
                var btnDiv = document.createElement("div");
                btnDiv.style.cssText = "margin:10px 0 5px 0; text-align:right";
                btnDiv.innerHTML = '<input type="submit" id="clearRecorderDumpsBtn" value="Clear Dumps" />';
                container.parentNode.insertBefore(btnDiv, container.nextSibling);
                document.getElementById("clearRecorderDumpsBtn").addEventListener("click", function() { clearRecorderDumps(); });
            }

            var bar = document.getElementById("tracy-debug-bar");
            var barHeight = bar ? bar.offsetHeight : 50;
            var maxH = window.innerHeight - barHeight - 10;
            panel.style.maxHeight = maxH + "px";
            if(panel.offsetTop + panel.offsetHeight > window.innerHeight - barHeight) {
                panel.style.top = Math.max(0, window.innerHeight - barHeight - panel.offsetHeight - 5) + "px";
            }
            if(firstSync || newEntries.length > 0) {
                var inner = container.closest(".tracy-inner");
                if(inner) inner.scrollTop = inner.scrollHeight;
            }
        }

        var badges = document.getElementsByClassName("dumpsRecorderCount");
        for(i = 0; i < badges.length; i++) {
            badges[i].textContent = entries.length > 0 ? entries.length : "";
        }
        if(entries.length > 0) {
            var iconPaths = document.getElementsByClassName("dumpsRecorderIconPath");
            for(i = 0; i < iconPaths.length; i++) {
                iconPaths[i].style.fill = window.TracyColorWarn || "#ff8309";
            }
        }
        lastSeenId = topId;
    }

    function escapeHtml(text) {
        var div = document.createElement("div");
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function init() {
        if(!document.getElementById("tracy-debug-bar")) {
            window.requestAnimationFrame(init);
            return;
        }
        // lastSeenId stays -1 until the first poll, which fully syncs the panel from
        // the polled entries (replacing the server-rendered set) so nothing is duplicated
        pollTimer = setTimeout(poll, pollInterval);
    }

    init();

    function resetToMinAndPollSoon() {
        pollInterval = minPollInterval;
        if(pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = setTimeout(poll, pollInterval);
        }
    }

    document.addEventListener("visibilitychange", function() {
        if(!document.hidden) resetToMinAndPollSoon();
    });

    // also reset on window focus so switching back from another app/window
    // gives fresh data fast, even when the tab was already visible
    window.addEventListener("focus", resetToMinAndPollSoon);

    window.addEventListener("beforeunload", function() {
        if(pollTimer) clearTimeout(pollTimer);
    });
})();
