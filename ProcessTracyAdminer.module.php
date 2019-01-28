<?php

class ProcessTracyAdminer extends Process implements Module, ConfigurableModule {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer', __FILE__),
            'summary' => __('Adminer page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '1.0.6',
            'autoload' => false,
            'singular' => true,
            'requires'  => 'ProcessWire>=2.7.2, PHP>=5.4.4, TracyDebugger',
            'icon' => 'database',
            'page' => array(
                'name' => 'adminer',
                'parent' => 'setup',
                'title' => 'Adminer'
            )
        );
    }


   /**
     * Default configuration for module
     *
     */
    static public function getDefaultData() {
        return array(
            "themeColor" => 'blue',
            "jsonMaxTextLength" => 500
        );
    }


    /**
     * Populate the default config data
     *
     */
    public function __construct() {
        foreach(self::getDefaultData() as $key => $value) {
            $this->$key = $value;
        }
    }


    public function ___execute() {

        error_reporting(0);
        ini_set('display_errors', 0);

        $_GET['db'] = $this->wire('config')->dbName;

        function adminer_object() {

            require_once './panels/Adminer/plugins/plugin.php';

            foreach (glob(__DIR__.'/panels/Adminer/plugins/*.php') as $filename) {
                require_once $filename/*NoCompile*/;
            }

            $data = wire('modules')->getModuleConfigData('ProcessTracyAdminer');
            $themeColor = isset($data['themeColor']) ? $data['themeColor'] : 'blue';
            $jsonMaxTextLength = isset($data['jsonMaxTextLength']) ? $data['jsonMaxTextLength'] : 200;

            $port = wire('config')->dbPort ? ':' . wire('config')->dbPort : '';

            $plugins = [
                new AdminerFrames,
                new AdminerProcessWireLogin(wire('config')->urls->admin, wire('config')->dbHost . $port, wire('config')->dbName, wire('config')->dbUser, wire('config')->dbPass, wire('config')->dbName),
                new AdminerTablesFilter(),
                new AdminerSimpleMenu(),
                new AdminerCollations(),
                new AdminerJsonPreview(0, true, true, $jsonMaxTextLength),
                new AdminerDumpJson,
                new AdminerDumpBz2,
                new AdminerDumpZip,
                new AdminerDumpAlter,
                new AdminerTheme("default-".$themeColor)
            ];

            return new AdminerPlugin($plugins);
        }

        require_once __DIR__ . '/panels/Adminer/adminer-4.7.1-mysql.php'/*NoCompile*/;
        exit;
    }

    /**
     * Return an InputfieldWrapper of Inputfields used to configure the class
     *
     * @param array $data Array of config values indexed by field name
     * @return InputfieldsWrapper
     *
     */
    public function getModuleConfigInputfields(array $data) {

        $wrapper = new InputfieldWrapper();

        $f = $this->wire('modules')->get("InputfieldSelect");
        $f->attr('name', 'themeColor');
        $f->label = __('Theme color', __FILE__);
        $f->addOption('blue', 'Blue');
        $f->addOption('green', 'Green');
        $f->addOption('orange', 'Orange');
        $f->required = true;
        if($this->data['themeColor']) $f->attr('value', $this->data['themeColor']);
        $wrapper->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'jsonMaxTextLength');
        $f->label = __('JSON max text length', __FILE__);
        $f->required = true;
        if($this->data['jsonMaxTextLength']) $f->attr('value', $this->data['jsonMaxTextLength']);
        $wrapper->add($f);

        return $wrapper;

    }

}
