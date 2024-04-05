<?php

class ProcessTracyAdminer extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer', __FILE__),
            'summary' => __('Adminer page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.0',
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

        $data = wire('modules')->getModuleConfigData('TracyDebugger');

        if(isset($data['adminerStandAlone']) && $data['adminerStandAlone'] === 1) {
            return $this->wire('modules')->get('ProcessTracyAdminerRenderer')->execute();
        }
        else {
            // push querystring to parent window
            return '
            <script>
                window.addEventListener("message", function(event) {
                    if(event.data && typeof event.data !== "object" && event.data.startsWith("username=&db=")) {
                        history.pushState(null, null, "?"+event.data);
                    }
                });
            </script>
            <iframe src="'.str_replace('/adminer/', '/adminer-renderer/', $_SERVER['REQUEST_URI']).'" style="width:100%; height:calc(100vh - 25px); border: none; padding:0; margin:0;"></iframe>';
        }
    }

}
