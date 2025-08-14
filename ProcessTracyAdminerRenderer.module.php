<?php

namespace ProcessWire {

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
            require_once __DIR__ . '/panels/Adminer/adminneo.php';
            exit;
        }
    }
}

// global namespace so adminneo.php can call \adminneo_instance()
namespace {

    function adminneo_instance() {

        foreach (glob(__DIR__.'/panels/Adminer/plugins/*.php') as $filename) {
            require_once $filename;
        }

        $tracyConfig = \ProcessWire\wire('modules')->getModuleConfigData('TracyDebugger');

        $plugins = [
            new \AdminNeo\ExternalLoginPlugin(true),
            new \AdminNeo\FrameSupportPlugin(["self"]),
            new \AdminNeo\ProcessWirePlugin(),
            new \AdminNeo\JsonDumpPlugin,
            new \AdminNeo\JsonPreviewPlugin(
                $tracyConfig['adminerJsonMaxLevel'],
                $tracyConfig['adminerJsonInTable'],
                $tracyConfig['adminerJsonInEdit'],
                $tracyConfig['adminerJsonMaxTextLength']
            ),
            new \AdminNeo\XmlDumpPlugin,
            new \AdminNeo\Bz2OutputPlugin,
            new \AdminNeo\ZipOutputPlugin
        ];

        $config = [
            "servers" => [
                [
                    "driver" => "mysql",
                    "server" => \ProcessWire\wire('config')->dbHost .
                        (\ProcessWire\wire('config')->dbPort ? ':' . \ProcessWire\wire('config')->dbPort : ''),
                    "database" => \ProcessWire\wire('config')->dbName,
                    "username" => \ProcessWire\wire('config')->dbUser,
                    "password" => \ProcessWire\wire('config')->dbPass
                ]
            ],
            "jsonValuesDetection" => true,
            "jsonValuesAutoFormat" => true,
            "preferSelection" => true,
            "colorVariant" => $tracyConfig['adminerThemeColor'],
            "cssUrls" => [
                \ProcessWire\wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/css/tweaks.css'
            ],
            "jsUrls" => [
                \ProcessWire\wire('config')->urls->root . 'site/modules/TracyDebugger/panels/Adminer/scripts/tweaks.js'
            ]
        ];

        return \AdminNeo\Admin::create($config, $plugins);
    }
}
