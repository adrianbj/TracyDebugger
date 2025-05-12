<?php

class ProcessTracyAdminerRenderer extends Process implements Module {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer Renderer', __FILE__),
            'summary' => __('Adminer renderer for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '2.0.3',
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

        if(version_compare(PHP_VERSION, '7.1.0', '>=')) {

            function adminneo_instance() {

                class CustomAdmin extends \AdminNeo\Admin {}

                foreach (glob(__DIR__.'/panels/Adminer/plugins/*.php') as $filename) {
                    require_once $filename/*NoCompile*/;
                }

                $tracyConfig = wire('modules')->getModuleConfigData('TracyDebugger');

                $plugins = [
                    new \AdminNeo\ExternalLoginPlugin(true),
                    new \AdminNeo\FrameSupportPlugin(["self"]),
                    new \AdminNeo\ProcessWirePlugin(),
                    new \AdminNeo\JsonDumpPlugin,
                    new \AdminNeo\JsonPreviewPlugin($tracyConfig['adminerJsonMaxLevel'], $tracyConfig['adminerJsonInTable'], $tracyConfig['adminerJsonInEdit'], $tracyConfig['adminerJsonMaxTextLength']),
                    new \AdminNeo\XmlDumpPlugin,
                    new \AdminNeo\Bz2OutputPlugin,
                    new \AdminNeo\ZipOutputPlugin
                ];

                $config = [
                    "servers" => [
                        [
                            "driver" => "mysql",
                            "server" => wire('config')->dbHost . (wire('config')->dbPort ? ':' . wire('config')->dbPort : ''),
                            "database" => wire('config')->dbName,
                            "username" => wire('config')->dbUser,
                            "password" => wire('config')->dbPass
                        ]
                    ],
                    "jsonValuesDetection" => true,
                    "jsonValuesAutoFormat" => true,
                    "preferSelection" => true,
                    "colorVariant" => $tracyConfig['adminerThemeColor'],
                    "cssUrls" => [
                        wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/css/tweaks.css'
                    ],
                    "jsUrls" => [
                        wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/scripts/tweaks.js'
                    ]
                ];

                return CustomAdmin::create($config, $plugins);
            }

            require_once __DIR__ . '/panels/Adminer/adminneo-mysql.php'/*NoCompile*/;

        }
        else {

            function adminer_object() {

                require_once './panels/Adminer/legacy/plugins/plugin.php';
                require_once './panels/Adminer/plugins/ProcessWirePlugin.php';

                foreach (glob(__DIR__.'/panels/Adminer/legacy/plugins/*.php') as $filename) {
                    require_once $filename/*NoCompile*/;
                }

                $tracyConfig = wire('modules')->getModuleConfigData('TracyDebugger');

                $plugins = [
                    new \AdminNeo\FrameSupportPlugin(["self"]),
                    new \AdminNeo\ProcessWirePlugin(),
                    new AdminerSimpleMenu(),
                    new AdminerCollations(),
                    new AdminerJsonPreview($tracyConfig['adminerJsonMaxLevel'], $tracyConfig['adminerJsonInTable'], $tracyConfig['adminerJsonInEdit'], $tracyConfig['adminerJsonMaxTextLength']),
                    new PrettyJsonEditPlugin,
                    new AdminerDumpAlter,
                    new AdminerDumpBz2,
                    new AdminerDumpDate,
                    new AdminerDumpJson,
                    new AdminerDumpPhp,
                    new AdminerDumpXml,
                    new AdminerDumpZip,
                    new AdminerTableHeaderScroll(),
                    new AdminerTheme("default-".str_replace('red', 'orange', $tracyConfig['adminerThemeColor']))
                ];

                return new AdminerPlugin($plugins);
            }

            require_once __DIR__ . '/panels/Adminer/legacy/adminer-mysql.php'/*NoCompile*/;

        }

        exit;
    }

}
