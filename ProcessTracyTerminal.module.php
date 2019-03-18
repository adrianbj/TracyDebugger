<?php

class ProcessTracyTerminal extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Terminal', __FILE__),
            'summary' => __('Terminal page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '1.0.0',
            'autoload' => false,
            'singular' => true,
            'requires'  => 'ProcessWire>=2.7.2, PHP>=5.4.4, TracyDebugger',
            'icon' => 'terminal',
            'page' => array(
                'name' => 'terminal',
                'parent' => 'setup',
                'title' => 'Terminal'
            )
        );
    }


    public function ___execute() {

        error_reporting(0);
        ini_set('display_errors', 0);

        require_once __DIR__ . '/panels/Terminal/shell.php'/*NoCompile*/;
        exit;
    }

}
