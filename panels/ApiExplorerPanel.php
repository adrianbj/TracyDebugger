<?php

use Tracy\Dumper;

class ApiExplorerPanel extends BasePanel {

    private $icon;
    private $apiBaseUrl;
    private $allClassesArr = array();
    private $tracyPwApiData;

    public function __construct() {
        if($this->wire('modules')->isInstalled('ProcessWireAPI')) {
            $apiModuleInstalled = true;
            $apiModuleId = $this->wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = $this->wire('pages')->get("process=$apiModuleId")->url.'methods/';
        }
        else {
            $apiModuleInstalled = false;
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

        $out .= <<< HTML

        <script>
            function removeA(arr) {
                var what, a = arguments, L = a.length, ax;
                while (L > 1 && arr.length) {
                    what = a[--L];
                    while ((ax= arr.indexOf(what)) !== -1) {
                        arr.splice(ax, 1);
                    }
                }
                return arr;
            }

            var groupShow = true;
            var manuallyOpened = [];
            function toggleVars() {

                var panel = document.getElementById("tracy-debug-panel-ApiExplorerPanel");
                var innerPanel = panel.getElementsByClassName("tracy-inner")[0];
                var sections = innerPanel.getElementsByTagName("div");
                Array.prototype.forEach.call(sections, function(el) {
                    elId = el.getAttribute("id");
                    if(groupShow) {
                        if(el.classList.contains("tracy-collapsed")) {
                            el.classList.toggle("tracy-collapsed", !groupShow);
                            removeA(manuallyOpened, elId);
                        }
                        else {
                            if(manuallyOpened.indexOf(elId) === -1) manuallyOpened.push(elId);
                        }
                    }
                    else {
                        if(manuallyOpened.indexOf(elId) === -1) {
                            el.classList.toggle("tracy-collapsed", !groupShow);
                        }
                    }
                });
                groupShow = !groupShow;
                window.Tracy.Debug.panels["tracy-debug-panel-ApiExplorerPanel"].reposition();
            }
        </script>
HTML;

        $out .= '
        <div class="tracy-inner">
            <p><input type="submit" id="toggleAll" onclick="toggleVars()" value="Toggle All" /></p>
        ';

        require_once __DIR__ . '/../includes/PwApiData.php';
        $this->tracyPwApiData = new TracyPwApiData();
        if(empty(\TracyDebugger::$allApiVars)) {
            \TracyDebugger::$allApiVars = $this->tracyPwApiData->getVariables();
        }

        $out .= '<h3>Variables</h3>';
        ksort(\TracyDebugger::$allApiVars);
        foreach(\TracyDebugger::$allApiVars as $var => $methods) {
            $out .= '
            <a href="#" rel="'.$var.'" class="tracy-toggle tracy-collapsed">$'.$var.'</a>
            <div style="padding-left:10px" id="'.$var.'" class="tracy-collapsed">' . $this->buildTable($var, $methods) . '</div><br />';
        }

        // Core classes
        $out .= $this->buildClasses($this->wire('config')->paths->core);

        // Core module classes
        if(in_array('coreModules', \TracyDebugger::getDataValue('apiExplorerModuleClasses'))) {
            $out .= $this->buildClasses($this->wire('config')->paths->modules);
        }

        // Site module classes
        if(in_array('siteModules', \TracyDebugger::getDataValue('apiExplorerModuleClasses'))) {
            $out .= $this->buildClasses($this->wire('config')->paths->siteModules);
        }

        $out .= \TracyDebugger::generatePanelFooter('apiExplorer', \Tracy\Debugger::timer('apiExplorer'), strlen($out), 'apiExplorerPanel');
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }


    private function buildClasses($folder) {

        if(strpos($folder, '/site/modules') !== false) {
            $classType = 'siteModule';
        }
        elseif(strpos($folder, 'modules') !== false) {
            $classType = 'coreModule';
        }
        else {
            $classType = 'core';
        }

        $classes = array();
        foreach(preg_grep('/^([^.])/', scandir($folder)) as $file) {
            array_push($classes, pathinfo($file, PATHINFO_FILENAME));
        }

        $classesArr = array();
        foreach($classes as $class) {
            if(!in_array($class, $this->allClassesArr)) {
                // for classes with an API object variable, provide an empty methods/properties array
                if(array_key_exists(lcfirst($class), array_keys(\TracyDebugger::$allApiVars))) {
                    $classesArr += array($class => array());
                }
                else {
                    if(!preg_match("/^[A-Z]/", $class)) continue;

                    if(class_exists("\ProcessWire\\$class")) {
                        $r = new \ReflectionClass("\ProcessWire\\$class");
                    }
                    elseif(class_exists($class)) {
                        $r = new \ReflectionClass($class);
                    }
                    else {
                        continue;
                    }
                    $classesArr += $this->tracyPwApiData->buildItemsArray($r, $class, $classType);
                }
                array_push($this->allClassesArr, $class);
            }
        }

        $out = '<h3>' . ucfirst($classType) . ' classes</h3>';
        ksort($classesArr);
        foreach($classesArr as $class => $methods) {
            $out .= '
            <a href="#" rel="'.$class.'" class="tracy-toggle tracy-collapsed">'.$class.'</a>
            <div style="padding-left:10px" id="'.$class.'" class="tracy-collapsed">' . $this->buildTable($class, $methods) . '</div><br />';
        }

        return $out;
    }


    private function buildTable($var, $items) {

        $class = is_object($this->wire($var)) ? get_class($this->wire($var)) : $var;
        $className = is_object($this->wire($var)) ? '$'.lcfirst($var) : $var;

        if(class_exists("\ProcessWire\\$class")) {
            $r = new \ReflectionClass("\ProcessWire\\$class");
        }
        elseif(class_exists($class)) {
            $r = new \ReflectionClass($class);
        }
        $filename = $r->getfilename();

        $out = '
        <table class="apiExplorerTable">';

        $out .= '
        <th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.lcfirst($var).' links</th>
        <tr>
            <td colspan="3"><a '.$this->newTab.' href="'.$this->apiBaseUrl.$this->tracyPwApiData->convertNamesToUrls(str_replace('$', '', $className)).'/">' . $className . '</a></td>';

        $out .= '
                <td>'.\TracyDebugger::createEditorLink(\TracyDebugger::removeCompilerFromPath($filename), 1, str_replace($this->wire('config')->paths->root, '', '/'.\TracyDebugger::removeCompilerFromPath($filename))).'</td>
            </tr>';

        if(empty($items)) return 'See <strong>$'.lcfirst($var).'</strong> api variable above for the properties and methods for this class';

        $i=0;
        $propertiesSection = false;
        foreach($items as $item => $info) {
            if(strpos($item, '()') === false && !$propertiesSection) {
                $propertiesSection = true;
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.lcfirst($var).' properties</th>';
            }
            elseif($i == 0) {
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.lcfirst($var).' methods</th>';
            }
            $out .= '
                <tr>
                    <td>'.str_replace('()', '', $info['name']).'</td>
                    <td>'.\TracyDebugger::createEditorLink(\TracyDebugger::removeCompilerFromPath($info['filename']), $info['lineNumber'], $info['lineNumber']).'</td>
                    <td class="tracy-force-no-wrap">' . $info['comment'] . '</td>';
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
        return $out;
    }

}
