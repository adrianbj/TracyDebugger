<?php

class ProcessTracyAdminer extends Process implements Module, ConfigurableModule {
    public static function getModuleInfo() {
        return array(
            'title' => __('Process Tracy Adminer', __FILE__),
            'summary' => __('Adminer page for TracyDebugger.', __FILE__),
            'author' => 'Adrian Jones',
            'href' => 'https://processwire.com/talk/topic/12208-tracy-debugger/',
            'version' => '1.1.3',
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
            "jsonMaxLevel" => 3,
            "jsonInTable" => 1,
            "jsonInEdit" => 1,
            "jsonMaxTextLength" => 200
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
            $data = array_merge(\ProcessTracyAdminer::getDefaultData(), $data);

            $port = wire('config')->dbPort ? ':' . wire('config')->dbPort : '';

            $plugins = [
                new AdminerFrames,
                new AdminerProcessWireLogin(wire('config')->urls->admin, wire('config')->dbHost . $port, wire('config')->dbName, wire('config')->dbUser, wire('config')->dbPass, wire('config')->dbName),
                new AdminerTablesFilter(),
                new AdminerSimpleMenu(),
                new AdminerCollations(),
                new AdminerJsonPreview($data['jsonMaxLevel'], $data['jsonInTable'], $data['jsonInEdit'], $data['jsonMaxTextLength']),
                new AdminerDumpJson,
                new AdminerDumpBz2,
                new AdminerDumpZip,
                new AdminerDumpAlter,
                new AdminerTheme("default-".$data['themeColor'])
            ];

            return new AdminerPlugin($plugins);
        }

        $_GET['username'] = '';
        require_once __DIR__ . '/panels/Adminer/adminer-4.8.0-mysql.php'/*NoCompile*/;
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
        $f->attr('name', 'jsonMaxLevel');
        $f->label = __('JSON max level', __FILE__);
        $f->description = __('Max. level in recursion. 0 means no limit.', __FILE__);
        $f->notes = __('Default: 3', __FILE__);
        $f->required = true;
        if($this->data['jsonMaxLevel']) $f->attr('value', $this->data['jsonMaxLevel']);
        $wrapper->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'jsonInTable');
        $f->label = __('JSON In Table', __FILE__);
        $f->description = __('Whether apply JSON preview in selection table.', __FILE__);
        $f->notes = __('Default: true', __FILE__);
        $f->attr('checked', $this->data['jsonInTable'] == '1' ? 'checked' : '');
        $wrapper->add($f);

        $f = $this->wire('modules')->get("InputfieldCheckbox");
        $f->attr('name', 'jsonInEdit');
        $f->label = __('JSON In Edit', __FILE__);
        $f->description = __('Whether apply JSON preview in edit form.', __FILE__);
        $f->notes = __('Default: true', __FILE__);
        $f->attr('checked', $this->data['jsonInEdit'] == '1' ? 'checked' : '');
        $wrapper->add($f);

        $f = $this->wire('modules')->get("InputfieldText");
        $f->attr('name', 'jsonMaxTextLength');
        $f->label = __('JSON max text length', __FILE__);
        $f->description = __('Maximal length of string values. Longer texts will be truncated with ellipsis sign. 0 means no limit.', __FILE__);
        $f->notes = __('Default: 200', __FILE__);
        $f->required = true;
        if($this->data['jsonMaxTextLength']) $f->attr('value', $this->data['jsonMaxTextLength']);
        $wrapper->add($f);

        return $wrapper;

    }

}
