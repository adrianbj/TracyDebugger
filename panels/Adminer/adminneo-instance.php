<?php
// adminneo.php includes this file when it's requested directly (on servers that don't block PHP files in
// site/modules), and without the instance it would fall back to a default AdminNeo login page for any database
// server. It's only meant to run inside ProcessWire, via ProcessTracyAdminerRenderer
if(!function_exists('ProcessWire\wire')) {
    http_response_code(404);
    exit;
}

// global namespace so adminneo.php can call \adminneo_instance()
function adminneo_instance() {

    foreach (glob(__DIR__.'/plugins/*.php') as $filename) {
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

    if(!empty($tracyConfig['adminerGeminiApiKey'])) {
        $plugins[] = new \AdminNeo\GeminiSqlPlugin(
            $tracyConfig['adminerGeminiApiKey'],
            !empty($tracyConfig['adminerGeminiModel']) ? $tracyConfig['adminerGeminiModel'] : 'gemini-2.5-flash'
        );
    }

    if(!empty($tracyConfig['adminerOpenWebUiApiUrl'])) {
        $plugins[] = new \AdminNeo\OpenWebUiPlugin(
            $tracyConfig['adminerOpenWebUiApiUrl'],
            !empty($tracyConfig['adminerOpenWebUiModel']) ? $tracyConfig['adminerOpenWebUiModel'] : 'gpt-oss:120b',
            !empty($tracyConfig['adminerOpenWebUiApiKey']) ? $tracyConfig['adminerOpenWebUiApiKey'] : null
        );
    }

    $config = \ProcessWire\TracyDebugger::getAdminerSslConfig() + [
        "servers" => [
            [
                "driver" => \ProcessWire\TracyDebugger::getAdminerDriver(),
                // shared with TracyDebugger::getAdminerAuthQuery(), which derives the
                // server key AdminNeo uses in its URLs from this same string
                "server" => \ProcessWire\TracyDebugger::getAdminerServer(),
                // breadcrumb label; AdminNeo falls back to the server string, which SQLite leaves empty
                "name" => \ProcessWire\TracyDebugger::getAdminerDriver() === 'sqlite' ? 'SQLite' : \ProcessWire\TracyDebugger::getAdminerServer(),
                "database" => \ProcessWire\TracyDebugger::getAdminerDatabase(),
                "username" => \ProcessWire\TracyDebugger::getAdminerUsername(),
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
