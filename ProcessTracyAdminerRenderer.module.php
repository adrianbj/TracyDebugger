<?php namespace ProcessWire;

class ProcessTracyAdminerRenderer extends Process implements Module {

    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer Renderer', __FILE__),
            'summary' => __('Adminer renderer for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.4',
            'autoload' => false,
            'singular' => true,
            'icon' => 'database',
            'requires'  => 'ProcessWire>=3.0.0, PHP>=7.1.0, TracyDebugger',
            'page' => array(
                'name' => 'adminer-renderer',
                'parent' => 'setup',
                'title' => 'Adminer Renderer',
                'status' => 'hidden'
            )
        );
    }

    public function ___execute() {
        require_once __DIR__ . '/panels/Adminer/adminneo-instance.php';
        require_once __DIR__ . '/panels/Adminer/adminneo.php';
        exit;
    }
}
