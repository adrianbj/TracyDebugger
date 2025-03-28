<?php

class ProcessTracyAdminer extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer', __FILE__),
            'summary' => __('Adminer page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.3',
            'autoload' => false,
            'singular' => true,
            'icon' => 'database',
            'requires'  => 'ProcessWire>=2.7.2, PHP>=5.4.4, TracyDebugger, ProcessTracyAdminerRenderer',
            'installs' => array('ProcessTracyAdminerRenderer'),
            'page' => array(
                'name' => 'adminer',
                'parent' => 'setup',
                'title' => 'Adminer'
            )
        );
    }

    public function ___execute() {

        $data = $this->wire('modules')->getModuleConfigData('TracyDebugger');

        if(isset($data['adminerStandAlone']) && $data['adminerStandAlone'] === 1) {
            return $this->wire('modules')->get('ProcessTracyAdminerRenderer')->execute();
        }
        else {
            // push querystring to parent window
            return '
            <iframe id="adminer-iframe" src="'.str_replace('/adminer/', '/adminer-renderer/', $_SERVER['REQUEST_URI']).'" style="width:100vw; border: none; padding:0; margin:0;"></iframe>
            <script>
                const adminer_iframe = document.getElementById("adminer-iframe");
                window.addEventListener("popstate", function (event) {
                    adminer_iframe.src = location.href.replace("/adminer/", "/adminer-renderer/");
                });

                const baseUrl = window.location.href.split("?")[0];
                const allowedOrigin = new URL(window.location.href).origin;
                const serviceTitle = document.title;
                const adminneoFrame = document.getElementById("adminer-iframe");

                window.addEventListener("message", function (event) {
                    if (!event.isTrusted || event.origin !== allowedOrigin || event.source !== adminneoFrame.contentWindow) {
                        return;
                }

                    const data = event.data;

                    if (typeof data === "object" && data.event === "adminneo-loading") {
                        const search = new URL(data.url).search;

                        document.title = `${data.title} - ${serviceTitle}`;
                        history.replaceState(null, null, baseUrl + search);
                    }
                });

                window.addEventListener("load", function(event) {
                    var masthead = document.getElementById("pw-masthead");
                    if (masthead && adminer_iframe) {
                        var mastheadHeight = masthead.offsetHeight;
                        adminer_iframe.style.height = `calc(100vh - ${mastheadHeight}px)`;
                    }
                });

                // determine scrollbarWidth
                var div = document.createElement("div");
                div.style.width = "100px";
                div.style.height = "100px";
                div.style.overflow = "scroll";
                div.style.visibility = "hidden";
                document.body.appendChild(div);
                const scrollbarWidth = div.offsetWidth - div.clientWidth;
                document.body.removeChild(div);

                // move tracy debug bar to up and left to accommodate iframe scrollbars if needed
                function adjustTracyDebugBar(iframe) {
                    if (!iframe) return;

                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!iframeDoc) return;

                    const body = iframeDoc.body;
                    const html = iframeDoc.documentElement;

                    const hasHorizontalScrollbar = body.scrollWidth > body.clientWidth || html.scrollWidth > html.clientWidth;
                    const hasVerticalScrollbar = body.scrollHeight > body.clientHeight || html.scrollHeight > html.clientHeight;

                    const tracyDebugBar = document.getElementById("tracy-debug-bar");

                    if (tracyDebugBar) {
                        if (hasHorizontalScrollbar) {

                            tracyDebugBar.style.setProperty("bottom", scrollbarWidth + "px", "important");
                        }
                        else {
                            tracyDebugBar.style.removeProperty("bottom");
                        }

                        if (hasVerticalScrollbar) {
                            tracyDebugBar.style.setProperty("right", scrollbarWidth + "px", "important");
                        }
                        else {
                            tracyDebugBar.style.removeProperty("right");
                        }
                    }
                }

                function observeIframeChanges(iframe) {
                    if (!iframe) return;

                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!iframeDoc) return;

                    const mutationObserver = new MutationObserver(() => adjustTracyDebugBar(iframe));
                    mutationObserver.observe(iframeDoc.body, { childList: true, subtree: true });

                    const resizeObserver = new ResizeObserver(() => adjustTracyDebugBar(iframe));
                    resizeObserver.observe(iframeDoc.documentElement);

                    iframe._tracyMutationObserver = mutationObserver;
                    iframe._tracyResizeObserver = resizeObserver;
                }

                if (adminer_iframe) {
                    adminer_iframe.onload = () => {
                        adjustTracyDebugBar(adminer_iframe);
                        observeIframeChanges(adminer_iframe);
                    };
                    window.addEventListener("resize", () => adjustTracyDebugBar(adminer_iframe));
                }
            </script>

            <style>
                #pw-content-head, #pw-content-title, #pw-footer, #notices {
                    display: none;
                }
                #main {
                    padding: 0 !important;
                    margin: 0 !important;
                }
                /* Fallback in case automatic JS adjustment fails - 73px is the height of the standard UiKit masthead */
                #adminer-iframe {
                    height: calc(100vh - 73px);
                }
            </style>';
        }
    }

}
