<?php

class ProcessTracyAdminer extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer', __FILE__),
            'summary' => __('Adminer page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.1',
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
            <iframe id="adminer-iframe" src="'.str_replace('/adminer/', '/adminer-renderer/', $_SERVER['REQUEST_URI']).'" style="width:calc(100vw - 80px); min-height:600px; border: none; padding:0; margin:0;"></iframe>
            <script>
                const adminer_iframe = document.getElementById("adminer-iframe");
                window.addEventListener("popstate", function (event) {
                    adminer_iframe.src = location.href.replace("/adminer/", "/adminer-renderer/");
                });
                window.addEventListener("message", function(event) {
                    if(!event.isTrusted) return;
                    if(event.source && event.origin === "'.trim($this->wire('config')->urls->httpRoot, '/').'" && event.source === adminer_iframe.contentWindow) {
                        if(event.data && typeof event.data === "string" && event.data.startsWith("username=&db=")) {
                            if(new URLSearchParams(window.location.search).toString() !== event.data) {
                                history.replaceState(null, null, "?"+event.data);
                            }
                        }
                        if(event.source.document.body && event.source.document.body.scrollHeight) {
                            adminer_iframe.style.height = (event.source.document.body.scrollHeight + 20) + "px";
                        }
                    }
                });
            </script>';
        }
    }

}
