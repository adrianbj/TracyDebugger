<?php namespace ProcessWire;

use Tracy\IBarPanel;
use Tracy\Debugger;

abstract class BasePanel extends WireData implements IBarPanel {

    /**
     * Build the standard tab span for the debug bar.
     *
     * @param string $title The tooltip title
     * @param string|null $label The visible label (defaults to $title)
     * @param string $badge Optional HTML badge (e.g. count span)
     * @return string
     */
    protected function buildTab($title, $label = null, $badge = '') {
        if($label === null) $label = $title;
        return '<span title="' . $title . '">' . $this->icon
            . (TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;' . $label : '')
            . $badge . '</span>';
    }

    /**
     * Build the panel header (h1 + optional resize button).
     *
     * @param string $title The panel title text
     * @param bool $showResize Whether to include the maximize/restore button
     * @param bool $showAdditionalBar Whether to append the additional bar identifier
     * @return string
     */
    protected function buildPanelHeader($title, $showResize = false, $showAdditionalBar = false) {
        $isAdditionalBar = $showAdditionalBar ? TracyDebugger::isAdditionalBar() : false;
        $out = '<h1>' . $this->icon . ' ' . $title
            . ($isAdditionalBar ? ' (' . $isAdditionalBar . ')' : '') . '</h1>';
        if($showResize) {
            $fqn = static::class;
            $pos = strrpos($fqn, '\\');
            $className = $pos !== false ? substr($fqn, $pos + 1) : $fqn;
            $out .= '<span class="tracy-icons"><span class="resizeIcons">'
                . '<a href="#" title="Maximize / Restore" data-tracy-resize="'
                . $className . ($isAdditionalBar ? '-' . $isAdditionalBar : '')
                . '">⛶</a></span></span>';
        }
        return $out;
    }

    /**
     * Open the panel inner div.
     *
     * @param string $extraClass Additional CSS class(es)
     * @param string $style Inline style string
     * @return string
     */
    protected function openPanel($extraClass = '', $style = '') {
        return '<div class="tracy-inner' . ($extraClass ? ' ' . $extraClass : '') . '"'
            . ($style ? ' style="' . $style . '"' : '') . '>';
    }

    /**
     * Close the panel with footer and closing div.
     *
     * @param string $out The accumulated panel HTML (used for size calculation)
     * @param string $panelName The panel identifier for timer/footer
     * @param string|null $settingsFieldsetId Optional settings fieldset link ID
     * @return string The complete panel HTML ready to return from getPanel()
     */
    protected function closePanel($out, $panelName, $settingsFieldsetId = null) {
        $out .= TracyDebugger::generatePanelFooter($panelName, Debugger::timer($panelName), strlen($out), $settingsFieldsetId);
        $out .= '</div>';
        return $out;
    }

    /**
     * REQUEST_URI to use for links and XHR targets built by this panel.
     *
     * Same reasoning as TracyDebugger::inputUrl(): while a deferred body renders, the endpoint's
     * own URI would send the panel's requests to the endpoint instead of the page the user is on.
     *
     * @return string
     */
    protected function panelRequestUri() {
        if(TracyDebugger::$renderingDeferredPanel && TracyDebugger::$deferredPanelUrl !== '') {
            return TracyDebugger::$deferredPanelUrl;
        }
        return isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    }


    /**
     * Loading shell for a panel whose body is fetched on first open.
     *
     * Returns '' while the tracyPanelContent endpoint is rendering, so the calling panel falls
     * through to building its real body. Otherwise returns the panel's own header plus a
     * tracy-inner placeholder and the script that replaces it.
     *
     * The header is kept in the shell deliberately: Tracy's Panel.init() captures the panel's
     * h1 elements as drag handles when the panel is first opened, so an h1 that only arrives
     * with the fetched body would leave the panel undraggable. Only the tracy-inner element is
     * swapped, and the fetched one brings its own classes and inline styles with it.
     *
     * @param string $header Panel header markup, as built by buildPanelHeader()
     * @param string $panelKey Key from TracyDebugger::$deferrablePanels
     * @return string
     */
    protected function deferredPanelShell($header, $panelKey) {
        // TracyDebugger::$deferrablePanels is the single switch: a panel whose key isn't listed
        // there renders inline, because the endpoint would 404 for it and the shell would sit on
        // "Could not load panel content" forever
        if(TracyDebugger::$renderingDeferredPanel) return '';
        if(!isset(TracyDebugger::$deferrablePanels[$panelKey])) return '';

        // carry the current page's URI along so the fetched body can build links back to it -
        // base64url'd into the same parameter, since a second "&" parameter would reach the JS
        // as "&amp;" after the panel HTML is escaped into its attribute
        $token = $panelKey;
        $originUrl = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if($originUrl !== '') $token .= '.' . rtrim(strtr(base64_encode($originUrl), '+/', '-_'), '=');
        $url = $this->wire('config')->urls->httpRoot . '?tracyPanelContent=' . urlencode($token);
        $id = 'tracyDeferred-' . $panelKey;
        $nonceAttr = TracyDebugger::getNonceAttr();

        // the placeholder carries a minimum size so the panel does not open as a small box and
        // then jump; Tracy positions a panel from its size at open time, and the swap below
        // re-anchors it afterwards
        return $header . '
        <div class="tracy-inner" id="' . $id . '" style="min-width: 320px; min-height: 100px">
            <div style="padding: 10px; color: ' . TracyDebugger::COLOR_LIGHTGREY . '">Loading&hellip;</div>
        </div>
        <script' . $nonceAttr . '>
        (function() {
            var placeholder = document.getElementById("' . $id . '");
            if(!placeholder || placeholder.dataset.tracyDeferredLoaded) return;
            placeholder.dataset.tracyDeferredLoaded = "1";
            fetch("' . $url . '", { credentials: "same-origin" })
                .then(function(response) { return response.ok ? response.text() : ""; })
                .then(function(html) {
                    if(!html) {
                        placeholder.innerHTML = "<div style=\'padding: 10px\'>Could not load panel content.</div>";
                        return;
                    }
                    var parsed = document.createElement("div");
                    parsed.innerHTML = html;
                    var container = placeholder.parentNode;
                    var panelElem = placeholder.closest ? placeholder.closest(".tracy-panel") : null;
                    if(!panelElem) panelElem = container;
                    var anchorRight = null, anchorBottom = null;
                    if(panelElem && panelElem.classList && (panelElem.classList.contains("tracy-mode-float") || panelElem.classList.contains("tracy-mode-peek"))) {
                        anchorRight = window.innerWidth - panelElem.offsetWidth - panelElem.offsetLeft;
                        anchorBottom = window.innerHeight - panelElem.offsetHeight - panelElem.offsetTop;
                    }
                    var scripts = Array.prototype.slice.call(parsed.querySelectorAll("script"));
                    scripts.forEach(function(script) { script.parentNode.removeChild(script); });
                    var fetchedInner = parsed.querySelector(".tracy-inner");
                    if(fetchedInner) {
                        container.replaceChild(fetchedInner, placeholder);
                    }
                    else {
                        placeholder.innerHTML = parsed.innerHTML;
                    }
                    scripts.forEach(function(oldScript) {
                        var newScript = document.createElement("script");
                        for(var i = 0; i < oldScript.attributes.length; i++) {
                            newScript.setAttribute(oldScript.attributes[i].name, oldScript.attributes[i].value);
                        }
                        newScript.appendChild(document.createTextNode(oldScript.textContent));
                        container.appendChild(newScript);
                    });
                    if(window.Tracy) {
                        if(window.Tracy.Dumper) window.Tracy.Dumper.init(container);
                        if(window.Tracy.TableSort) window.Tracy.TableSort.init();
                        if(window.Tracy.Tabs) window.Tracy.Tabs.init();
                        var panels = window.Tracy.Debug ? window.Tracy.Debug.panels : null;
                        var panelObj = (panels && panelElem && panelElem.id) ? panels[panelElem.id] : null;
                        if(anchorRight !== null) {
                            var winWidth = window.innerWidth, winHeight = window.innerHeight;
                            var left = winWidth - panelElem.offsetWidth - anchorRight;
                            var top = winHeight - panelElem.offsetHeight - anchorBottom;
                            panelElem.style.left = Math.max(0, Math.min(left, winWidth - panelElem.offsetWidth)) + "px";
                            panelElem.style.top = Math.max(0, Math.min(top, winHeight - panelElem.offsetHeight)) + "px";
                            if(panelObj && panelObj.savePosition) panelObj.savePosition();
                        }
                        else if(panelObj && panelObj.reposition) {
                            panelObj.reposition();
                        }
                    }
                })
                .catch(function() {
                    placeholder.innerHTML = "<div style=\'padding: 10px\'>Could not load panel content.</div>";
                });
        })();
        </script>';
    }

    /**
     * Generate a CSRF hidden input field.
     *
     * @return string
     */
    protected function csrfInput() {
        return '<input type="hidden" name="' . $this->wire('session')->CSRF->getTokenName()
            . '" value="' . $this->wire('session')->CSRF->getTokenValue() . '" />';
    }

    /**
     * Get the reference page (edited page in admin, or current page on frontend).
     *
     * @param array|null $processTypes Process class names to check (defaults to common set)
     * @return Page
     */
    protected function getReferencePage($processTypes = null) {
        if($processTypes === null) {
            $processTypes = array('ProcessPageEdit', 'ProcessUser', 'ProcessRole', 'ProcessPermission', 'ProcessLanguage');
        }
        if(TracyDebugger::getDataValue('referencePageEdited')
            && $this->wire('input')->get('id')
            && in_array((string)$this->wire('process'), $processTypes)
        ) {
            $p = $this->wire('process')->getPage();
            if(!$p || $p instanceof NullPage) {
                $p = $this->wire('pages')->get((int) $this->wire('input')->get('id'));
            }
            if(!$p || $p instanceof NullPage) {
                return $this->wire('page');
            }
            return $p;
        }
        return $this->wire('page');
    }

    /**
     * Build a section header with a table and column headings.
     *
     * @param array $columnNames Column heading labels
     * @param string $thStyle Optional inline style for th elements
     * @return string
     */
    protected function sectionEnd() {
        return '</tbody></table></div>';
    }

    protected function sectionHeader($columnNames = array()) {
        $out = '<div><table><thead><tr>';
        foreach($columnNames as $columnName) {
            $out .= '<th>' . $columnName . '</th>';
        }
        $out .= '</tr></thead><tbody>';
        return $out;
    }

    /**
     * Strip the site root path from an absolute path.
     *
     * @param string $path The absolute file path
     * @param string $prefix What to replace the root with (default '/')
     * @return string
     */
    protected function stripRootPath($path, $prefix = '/') {
        return TracyDebugger::stripRootPath($path, $prefix);
    }

    /**
     * Markdown summary surfaced to AI agents via Tracy 2.12+ Bar::renderAgent().
     * Panels override to opt in; returning null omits the panel from agent output.
     *
     * @return string|null
     */
    public function getAgentInfo(): ?string {
        return null;
    }

    /**
     * Run a callable inside try/catch; on Throwable, log to Tracy and return $fallback.
     * Use when you're rendering a single fragment (cell, list item, link) and want to keep
     * rendering the rest of the panel even if one fragment's source code misbehaves.
     *
     * If $fallback is a string containing '%s', the throwable's HTML-escaped message is
     * interpolated into it.
     *
     * @param callable $cb
     * @param mixed    $fallback
     * @return mixed
     */
    protected function safeRender(callable $cb, $fallback = '') {
        try {
            return $cb();
        }
        catch(\Throwable $e) {
            TD::log($e);
            if(is_string($fallback) && strpos($fallback, '%s') !== false) {
                return sprintf($fallback, htmlspecialchars($e->getMessage(), ENT_QUOTES));
            }
            return $fallback;
        }
    }

    /**
     * Iterate $items, calling $renderItem($item, $key) for each and concatenating the
     * returned HTML. If $renderItem throws on any item, that item is replaced with
     * $errorTemplate (sprintf'd with the throwable's HTML-escaped message), the error
     * is logged to Tracy, and iteration continues.
     *
     * Use for the common pattern of "render a row per page/field/template/hook" where
     * a single broken item shouldn't take down the whole table.
     *
     * @param iterable $items
     * @param callable $renderItem function($item, $key): string
     * @param string   $errorTemplate sprintf template; '%s' is replaced with error message.
     *                                Default targets a generic table-cell context.
     * @return string
     */
    protected function safeIterate(iterable $items, callable $renderItem, $errorTemplate = '<tr><td colspan="99"><em style="color:#c00">error: %s</em></td></tr>') {
        $out = '';
        foreach($items as $key => $item) {
            try {
                $out .= $renderItem($item, $key);
            }
            catch(\Throwable $e) {
                TD::log($e);
                $out .= sprintf($errorTemplate, htmlspecialchars($e->getMessage(), ENT_QUOTES));
            }
        }
        return $out;
    }

}
