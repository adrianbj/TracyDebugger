<?php

use Adminer\Pluginer;
use Adminer\AdminerProcessWireLogin;

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

        function create_adminer(): Pluginer {

            foreach (glob(__DIR__.'/panels/Adminer/plugins/*.php') as $filename) {
                require_once $filename/*NoCompile*/;
            }

            $tracyConfig = wire('modules')->getModuleConfigData('TracyDebugger');

            $plugins = [
                new \Adminer\AdminerFrames,
                new AdminerProcessWireLogin(),
                new \Adminer\AdminerJsonPreview($tracyConfig['adminerJsonMaxLevel'], $tracyConfig['adminerJsonInTable'], $tracyConfig['adminerJsonInEdit'], $tracyConfig['adminerJsonMaxTextLength']),
                new \Adminer\AdminerDumpAlter,
                new \Adminer\AdminerDumpBz2,
                new \Adminer\AdminerDumpDate,
                new \Adminer\AdminerDumpJson,
                new \Adminer\AdminerDumpPhp,
                new \Adminer\AdminerDumpXml,
                new \Adminer\AdminerDumpZip,
                new \Adminer\AdminerTableHeaderScroll()
            ];

            $config = [
                "preferSelection" => true,
                "cssUrls" => [
                    wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/css/tweaks.css'
                ],
                "jsUrls" => [
                    wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/scripts/tweaks.js'
                ]
            ];

            return new Pluginer($plugins, $config);
        }

        require_once __DIR__ . '/panels/Adminer/adminer-mysql.php'/*NoCompile*/;
        exit;
    }

}
