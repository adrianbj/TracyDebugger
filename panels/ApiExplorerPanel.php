<?php

use Tracy\Dumper;

class ApiExplorerPanel extends BasePanel {

    private $icon;
    private $apiBaseUrl;
    private $tracyPwApiData;

    public function __construct() {
        if($this->wire('modules')->isInstalled('ProcessWireAPI')) {
            $this->apiModuleInstalled = true;
            $apiModuleId = $this->wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = $this->wire('pages')->get("process=$apiModuleId")->url.'methods/';
        }
        else {
            $this->apiModuleInstalled = false;
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
        $this->newTab = \TracyDebugger::getDataValue('linksNewTab') ? 'target="_blank"' : '';
    }


    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('apiExplorer');

        $this->icon = '
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="387.3 297.2 16.9 17.5">
            <path stroke="'.\TracyDebugger::COLOR_NORMAL.'" stroke-width="1.0616" stroke-miterlimit="10" d="M390.9 303.1h-1.6c-.7 0-1.2.5-1.2 1.2v4c0 .2.2.4.4.4s.4-.2.4-.4v-1.2h2.4v1.2c0 .2.2.4.4.4s.4-.2.4-.4v-4c0-.7-.5-1.2-1.2-1.2zm.4 3.2h-2.4v-2c0-.2.2-.4.4-.4h1.6c.2 0 .4.2.4.4v2zM396.1 303.1h-1.6c-.2 0-.4.2-.4.4v4.9c0 .2.2.4.4.4s.4-.2.4-.4v-1.2h1.2c1.1 0 2-.9 2-2s-.8-2.1-2-2.1zm0 3.2h-1.2v-2.4h1.2c.7 0 1.2.5 1.2 1.2.1.7-.5 1.2-1.2 1.2zM403 308h-1.2v-4h1.2c.2 0 .4-.2.4-.4s-.2-.4-.4-.4h-3.2c-.2 0-.4.2-.4.4s.2.4.4.4h1.2v4h-1.2c-.2 0-.4.2-.4.4s.2.4.4.4h3.2c.2 0 .4-.2.4-.4s-.2-.4-.4-.4z"/>
            <path stroke="'.\TracyDebugger::COLOR_NORMAL.'" stroke-width=".913" stroke-miterlimit="10" d="M402.7 299.1h-5.4l-1.3-1.3c-.1-.1-.4-.1-.5 0l-1.3 1.3h-5.4c-.6 0-1 .5-1 1v1c0 .2.2.3.3.3s.3-.2.3-.3v-1c0-.2.2-.3.3-.3h5.6c.1 0 .2 0 .2-.1l1.1-1.1 1.1 1.1c.1.1.2.1.2.1h5.6c.2 0 .3.2.3.3v1c0 .2.2.3.3.3s.3-.2.3-.3v-1c.3-.5-.1-1-.7-1z"/>
            <path stroke="'.\TracyDebugger::COLOR_NORMAL.'" stroke-width=".913" stroke-miterlimit="10" d="M403.4 310.5c-.2 0-.3.2-.3.3v1c0 .2-.2.3-.3.3h-5.6c-.1 0-.2 0-.2.1l-1.1 1.1-1.1-1.1c-.1-.1-.2-.1-.2-.1H389c-.2 0-.3-.2-.3-.3v-1c0-.2-.2-.3-.3-.3s-.3.2-.3.3v1c0 .6.5 1 1 1h5.4l1.3 1.3c.1.1.2.1.2.1.1 0 .2 0 .2-.1l1.3-1.3h5.4c.6 0 1-.5 1-1v-1c-.2-.2-.3-.3-.5-.3z"/>
        </svg>';

        return '
        <span title="API Explorer">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;API Explorer' : '') . '
        </span>';
    }


    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' API Explorer</h1><span class="tracy-icons"><span class="resizeIcons"><a href="#" title="Maximize / Restore" onclick="tracyResizePanel(\'ApiExplorerPanel\')">+</a></span></span>';

        $tracyModuleUrl = $this->wire('config')->urls->TracyDebugger;
        $out .= <<< HTML
        <script>
            tracyJSLoader.load("{$tracyModuleUrl}scripts/filterbox/filterbox.js", function() {
                tracyJSLoader.load("{$tracyModuleUrl}scripts/api-explorer-search.js");
            });
        </script>
HTML;

        $out .= '
        <div class="tracy-inner">';

        // variables
        $currentApiOut = $this->buildTypes('variables');

        // core classes
        $currentApiOut .= $this->buildTypes('core');

        // procedural functions
        $currentApiOut .= $this->buildTypes('proceduralFunctions');

        // core module classes
        if(in_array('coreModules', \TracyDebugger::getDataValue('apiExplorerModuleClasses'))) {
            $currentApiOut .= $this->buildTypes('coreModules');
        }

        // site module classes
        if(in_array('siteModules', \TracyDebugger::getDataValue('apiExplorerModuleClasses'))) {
            $currentApiOut .= $this->buildTypes('siteModules');
        }


        //get API changes
        $apiChangesOut = '';
        $apiChanges = $this->wire('cache')->get('TracyApiChanges');
        $apiChanges = json_decode(ltrim($apiChanges, '~'), true);
        if(is_array($apiChanges) && count($apiChanges) > 1) {
            foreach($apiChanges as $type => $classes) {
                if($type == 'cachedVersion') {
                    $apiChangesOut .= '
                    <a href="#" rel="new-since" class="tracy-toggle tracy-collapsed new-since">
                        <span style="font-size: 15px; font-weight: bold">NEW SINCE v'.$classes.'</span>
                    </a>
                    <div style="padding-left:10px" id="new-since" class="tracy-collapsed new-since">
                    ';
                    continue;
                }
                if(!isset($currentType) || $currentType !== $type) {
                    $apiChangesOut .= '<h3>' . ucfirst(strtolower(preg_replace('/([a-z0-9])([A-Z])/', "$1 $2", $type))) .($type == 'variables' || $type == 'proceduralFunctions' ? '' : ' classes').'</h3>';
                }
                foreach($classes as $class => $methods) {
                    foreach($methods as $method) {
                        $apiChangesOut .= ($type == 'variables' ? '$' : '').$class.'->'.$method .'<br />';
                    }
                }
                $currentType = $type;
            }
            $apiChangesOut .= '</div><br /><br /><span style="font-size: 15px; font-weight: bold">CURRENT v'.$this->wire('config')->version.'</span>';
        }

        $out .= $apiChangesOut . $currentApiOut;

        $out .= \TracyDebugger::generatePanelFooter('apiExplorer', \Tracy\Debugger::timer('apiExplorer'), strlen($out), 'apiExplorerPanel');
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }


    private function buildTypes($type) {
        $out = '<h3>' . ucfirst(strtolower(preg_replace('/([a-z0-9])([A-Z])/', "$1 $2", $type))) .($type == 'variables' || $type == 'proceduralFunctions' ? '' : ' classes').'</h3>';
        foreach(\TracyDebugger::getApiData($type) as $class => $methods) {
            $out .= $this->buildTable($class, $methods, $type);
        }
        return $out;
    }


    private function buildTable($var, $items, $type) {

        if(is_object($this->wire($var))) {
            $class = get_class($this->wire($var));
            $className =  '$'.lcfirst($var);
        }
        elseif($var == 'FunctionsAPI') {
            $class = 'Functions';
            $className = 'Functions';
        }
        else {
            $class = $var;
            $className = $var;
        }

        $varTitle = $type == 'variables' ? '$'.lcfirst($var) : $var;

        if(class_exists("\ProcessWire\\$class")) {
            $r = new \ReflectionClass("\ProcessWire\\$class");
        }
        elseif(class_exists($class)) {
            $r = new \ReflectionClass($class);
        }

        $filename = isset($r) ? $r->getfilename() : $this->wire('config')->paths->core . $var . '.php';

        $out = '
        <table class="apiExplorerTable">';

        $out .= '
        <th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">'.$varTitle.' links</th>
        <tr>
            <td colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '3' : '2').'"><a '.$this->newTab.' href="'.$this->apiBaseUrl.$this->convertNamesToUrls(str_replace('$', '', $className)).'/">' . $className . '</a></td>';

        $out .= '
                <td>'.\TracyDebugger::createEditorLink(\TracyDebugger::removeCompilerFromPath($filename), 1, str_replace($this->wire('config')->paths->root, '', '/'.\TracyDebugger::removeCompilerFromPath($filename))).'</td>
            </tr>';

        if(empty($items)) {
            $out = 'See <strong>$'.lcfirst($class).'</strong> api variable above for the properties and methods for this class';
        }
        else {
            $i=0;
            $propertiesSection = false;
            foreach($items as $item => $info) {
                if(strpos($item, '()') === false && !$propertiesSection) {
                    $propertiesSection = true;
                    $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">'.$varTitle. ($class == 'Functions' ? '' : ' properties').'</th>';
                }
                elseif($i == 0) {
                    $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">'.$varTitle.' methods</th>';
                }

                $name = $info['name'];
                $methodName = str_replace(array('___', '__'), '', $name);
                if(strpos($filename, 'wire') !== false || $this->apiModuleInstalled) {
                    $name = "<a ".$this->newTab." href='".$this->apiBaseUrl.$this->convertNamesToUrls(str_replace('$', '', $className))."/".$this->convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                }

                $out .= '
                    <tr>
                        <td>'.str_replace('()', '', $name).'</td>
                        <td>'.\TracyDebugger::createEditorLink(\TracyDebugger::removeCompilerFromPath($filename), $info['lineNumber'], $info['lineNumber']).'</td>
                        <td class="tracy-force-no-wrap">' . (isset($info['comment']) ? $info['comment'] : '') . '</td>';
                    if(\TracyDebugger::getDataValue('apiExplorerShowDescription') && isset($info['description'])) {
                        $out .= '
                        <td class="tracy-force-no-wrap">' . $info['description'] . '</td>';
                    }
                $out .=
                    '</tr>';
                    $i++;
            }
            $out .= '
                </table>
            ';
        }

        return '
        <a href="#" rel="'.$var.'" class="tracy-toggle tracy-collapsed">'.($type == 'variables' ? '$' : '').$var.'</a>
        <div id="'.$var.'" class="tracy-collapsed">' . $out . '</div>';

    }


    private static function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}