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
        return '<iframe src="'.str_replace('/adminer/', '/adminer-renderer/', wire('input')->url(true)).'" style="width:100%; height:calc(100vh - 25px); border: none; padding:0; margin:0;"></iframe>';
    }

}
