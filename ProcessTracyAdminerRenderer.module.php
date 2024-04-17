<?php

class ProcessTracyAdminerRenderer extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer Renderer', __FILE__),
            'summary' => __('Adminer renderer for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.2',
            'autoload' => false,
            'singular' => true,
            'icon' => 'database',
            'requires'  => 'ProcessWire>=2.7.2, PHP>=5.4.4, TracyDebugger',
            'page' => array(
                'name' => 'adminer-renderer',
                'parent' => 'setup',
                'title' => 'Adminer Renderer',
                'status' => 'hidden'
            )
        );
    }


    public function ___execute() {

        $_GET['db'] = $this->wire('config')->dbName;

        function adminer_object() {

            require_once './panels/Adminer/plugins/plugin.php';

            foreach (glob(__DIR__.'/panels/Adminer/plugins/*.php') as $filename) {
                require_once $filename/*NoCompile*/;
            }

            $data = wire('modules')->getModuleConfigData('TracyDebugger');

            $port = wire('config')->dbPort ? ':' . wire('config')->dbPort : '';

            $plugins = [
                new AdminerFrames,
                new AdminerProcessWireLogin(wire('config')->urls->admin, wire('config')->dbHost . $port, wire('config')->dbName, wire('config')->dbUser, wire('config')->dbPass, wire('config')->dbName),
                new AdminerTablesFilter(),
                new AdminerSimpleMenu(),
                new AdminerCollations(),
                new AdminerJsonPreview($data['adminerJsonMaxLevel'], $data['adminerJsonInTable'], $data['adminerJsonInEdit'], $data['adminerJsonMaxTextLength']),
                new AdminerDumpJson,
                new AdminerDumpBz2,
                new AdminerDumpZip,
                new AdminerDumpAlter,
                new AdminerTableHeaderScroll(),
                new AdminerTheme("default-".$data['adminerThemeColor'])
            ];

            return new AdminerPlugin($plugins);
        }

        $_GET['username'] = '';
        require_once __DIR__ . '/panels/Adminer/adminer-mysql.php'/*NoCompile*/;
        exit;
    }

}
