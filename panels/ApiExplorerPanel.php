<?php

use Tracy\Dumper;

class ApiExplorerPanel extends BasePanel {

    protected $icon;
    protected $apiBaseUrl;

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

        // API variables
        $apiVariables = array();
        $pwVars = $this->wire('config')->version >= 2.8 ? $this->wire('all') : $this->wire()->fuel;
        if(is_object($pwVars)) {
            $apiVars = array();
            foreach($pwVars as $key => $value) {
                if(!is_object($value)) continue;
                $apiVars[$key] = $value;
            }
            ksort($apiVars);
            foreach($apiVars as $key => $value) {
                $r = new \ReflectionObject($this->wire()->$key);
                $apiVariables += $this->buildItemsArray($r, $key);
            }
        }

        $out .= '<h3>Variables</h3>';
        ksort($apiVariables);
        foreach($apiVariables as $var => $methods) {
            $out .= '
            <a href="#" rel="'.$var.'" class="tracy-toggle tracy-collapsed">$'.$var.'</a>
            <div style="padding-left:10px" id="'.$var.'" class="tracy-collapsed">' . $this->buildTable($var, $methods) . '</div><br />';
        }


        // Core classes without API variable
        $classes = array();
        $coreFiles = preg_grep('/^([^.])/', scandir($this->wire('config')->paths->core));
        foreach($coreFiles as $file) {
            array_push($classes, pathinfo($file, PATHINFO_FILENAME));
        }

        $coreClasses = array();
        foreach($classes as $class) {

            // for classes with an API object variable, provide an methods/properties array
            if(array_key_exists(lcfirst($class), $apiVars)) {
                $coreClasses += array($class => array());
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
                $coreClasses += $this->buildItemsArray($r, $class);
            }
        }

        $out .= '<h3>Core Classes (without PW variable)</h3>';
        ksort($coreClasses);
        foreach($coreClasses as $class => $methods) {
            $out .= '
            <a href="#" rel="'.$class.'" class="tracy-toggle tracy-collapsed">'.$class.'</a>
            <div style="padding-left:10px" id="'.$class.'" class="tracy-collapsed">' . $this->buildTable($class, $methods) . '</div><br />';
        }


        $out .= \TracyDebugger::generatePanelFooter('apiExplorer', \Tracy\Debugger::timer('apiExplorer'), strlen($out), 'apiExplorerPanel');
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }


    private function buildItemsArray($r, $key) {
        $items = array();
        $methods = $r->getMethods();

        // get runtime properties from doc comment
        $classDocComment = $r->getDocComment();
        // get the comment
        preg_match('#^/\*\*(.*)\*/#s', $classDocComment, $comment);
        if(isset($comment[0])) $comment = trim($comment[0]);
        // get all the lines and strip the * from the first character
        if(is_string($comment)) {
            preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
            $propertiesList = array();
            foreach($commentLines[1] as $c) {
                if(strpos($c, '@property') !== false) {
                    preg_match('/(?:@property)(?:\s)(?:\S*)(?:\s)(\$*[A-Za-z_]+)(?:\s)([#A-Za-z`\$->\'’()\s{}]+)/', $c, $varName);
                    if(isset($varName[1])) {
                        $propertiesList[str_replace('$', '', $varName[1])]['name'] = str_replace('$', '', $varName[1]);
                        $propertiesList[str_replace('$', '', $varName[1])]['description'] = $varName[2];
                    }
                }
            }
        }

        if(isset($propertiesList)) {

            uksort($propertiesList, function($a, $b) {
                $aStripped = str_replace(array('___','__', '_'), '', $a);
                $bStripped = str_replace(array('___','__', '_'), '', $b);
                return $aStripped > $bStripped;
            });

            foreach($propertiesList as $name => $info) {
                $items[$key][$name]['name'] = $name;
                $items[$key][$name]['lineNumber'] = '';
                $items[$key][$name]['filename'] = '';
                $items[$key][$name]['comment'] = "$" . lcfirst($key) . '->' . str_replace('___', '', $name);
                if(\TracyDebugger::getDataValue('apiExplorerShowDescription')) {
                    $desc = preg_replace('/#([^#\s]+)/', '', $info['description']);
                    $items[$key][$name]['description'] = $desc;
                }
            }
        }


        // methods from reflection
        $methodsList = array();
        foreach($methods as $m) {
            $name = $m->name;
            if(!$r->getMethod($name)->isPublic()) continue;

            // if method is inherited from Wire/WireData/WireArray, then don't display
            $declaringClassName = str_replace('ProcessWire\\', '', $m->getDeclaringClass()->getName());
            if(!\TracyDebugger::getDataValue('apiExplorerIncludeInheritedMethods')) {
                if(strcasecmp($key, 'Wire') !== 0 && strcasecmp($declaringClassName, 'Wire') == 0) continue;
                if(strcasecmp($key, 'WireData') !== 0 && strcasecmp($declaringClassName, 'WireData') == 0) continue;
                if(strcasecmp($key, 'WireArray') !== 0 && strcasecmp($declaringClassName, 'WireArray') == 0) continue;
            }

            $docComment = $r->getMethod($name)->getDocComment();
            $filename = $r->getMethod($name)->getFilename();
            $className = $key;
            $methodName = str_replace(array('___', '__'), '', $name);

            if(strpos($docComment, '#pw-internal') === false && strpos($filename, 'wire') !== false) {
                if($this->apiModuleInstalled || strpos($filename, 'modules') === false) {
                    $methodsList[$name.'()']['name'] = "<a ".$this->newTab." href='".$this->apiBaseUrl.self::convertNamesToUrls($className)."/".self::convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                }
                else {
                    $methodsList[$name.'()']['name'] = $name;
                }
            }
            else {
                $methodsList[$name.'()']['name'] = $name;
            }

            $methodsList[$name.'()']['lineNumber'] = $r->getMethod($name)->getStartLine();
            $methodsList[$name.'()']['filename'] = $filename;
            $i=0;
            foreach($r->getMethod($name)->getParameters() as $param) {
                $methodsList[$name.'()']['params'][$i] = ($param->isOptional() ? '<em>' : '') . '$' . $param->getName() . ($param->isOptional() ? '</em>' : '');
                $i++;
            }

            $methodStr = "$" . lcfirst($key) . '->' . str_replace('___', '', $name) . '(' . (isset($methodsList[$name.'()']['params']) ? implode(', ', $methodsList[$name.'()']['params']) : '') . ')';

            if(\TracyDebugger::getDataValue('apiExplorerToggleDocComment')) {
                $commentStr = "
                <div id='ch-comment'>
                    <label class='".($docComment != '' ? 'comment' : '') . "' for='".$key."_".$name."'>".$methodStr."</label>
                    <input type='checkbox' id='".$key."_".$name."'>
                    <div class='hide'>";
                $methodsList[$name.'()']['comment'] = $commentStr . "\n\n" . nl2br(htmlentities($docComment)) .
                        "</div>
                    </div>";
            }
            else {
                $methodsList[$name.'()']['comment'] = $methodStr;
            }

            if(\TracyDebugger::getDataValue('apiExplorerShowDescription')) {
                // get the comment
                preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment);
                if(isset($comment[0])) $comment = trim($comment[0]);
                // get all the lines and strip the * from the first character
                if(is_string($comment)) {
                    preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
                    if(isset($commentLines[1][0])) $desc = preg_replace('/#([^#\s]+)/', '', $commentLines[1][0]);
                    $methodsList[$name.'()']['description'] = isset($desc) && is_string($desc) ? nl2br(htmlentities(trim($desc))) : '';
                }
                else {
                    $methodsList[$name.'()']['description'] = '';
                }
            }

        }

        // get runtime methods from doc comment
        $classDocComment = $r->getDocComment();
        // get the comment
        preg_match('#^/\*\*(.*)\*/#s', $classDocComment, $comment);
        if(isset($comment[0])) $comment = trim($comment[0]);
        // get all the lines and strip the * from the first character
        if(is_string($comment)) {
            preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
            foreach($commentLines[1] as $c) {
                if(strpos($c, '@method') !== false) {
                    preg_match('/(.*)(?:\s)([A-Za-z_]+)(\([^)]*\))(?:\s+)(.*)$/U', $c, $varName);
                    if(isset($varName[2]) && !array_key_exists($varName[2], $methodsList)) {
                        $methodsList[$varName[2].'()']['name'] = $varName[2];
                        $methodsList[$varName[2].'()']['params'] = explode(', ', str_replace(array('(', ')'), '', $varName[3]));
                        $methodsList[$varName[2].'()']['description'] = preg_replace('/#([^#\s]+)/', '', $varName[4]);
                    }
                }
            }
        }

        if(isset($methodsList)) {

            uksort($methodsList, function($a, $b) {
                $aStripped = str_replace(array('___','__', '_'), '', $a);
                $bStripped = str_replace(array('___','__', '_'), '', $b);
                return $aStripped > $bStripped;
            });

            foreach($methodsList as $name => $info) {
                $items[$key][$name]['name'] = $name;
                $items[$key][$name]['lineNumber'] = isset($info['lineNumber']) ? $info['lineNumber'] : '';
                $items[$key][$name]['filename'] = isset($info['filename']) ? $info['filename'] : '';
                if(isset($info['comment'])) {
                    $methodStr = $info['comment'];
                }
                else {
                    $methodStr = "$" . lcfirst($key) . '->' . str_replace(array('___', '()'), '', $name) . '(' . (isset($info['params']) ? implode(', ', $info['params']) : '') . ')';
                }
                $items[$key][$name]['comment'] = $methodStr;

                if(\TracyDebugger::getDataValue('apiExplorerShowDescription')) {
                    if(substr($info['description'], 0, 1) === '#') {
                        $items[$key][$name]['description'] = '';
                    }
                    else {
                        $items[$key][$name]['description'] = $info['description'];
                    }
                }
            }
        }

        return $items;

    }


    private function buildTable($var, $items) {
        $out = '
            <table class="apiExplorerTable">';

        if(empty($items)) return 'See <strong>$'.lcfirst($var).'</strong> api variable above for the properties and methods for this class';

        $i=0;
        $methodsSection = false;
        foreach($items as $item => $info) {
            if(strpos($item, '()') !== false && !$methodsSection) {
                $methodsSection = true;
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.lcfirst($var).' methods</th>';
            }
            elseif($i == 0) {
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.lcfirst($var).' properties</th>';
            }
            $out .= '
                <tr>
                    <td>'.str_replace('()', '', $info['name']).'</td>
                    <td>'.\TracyDebugger::createEditorLink($info['filename'], $info['lineNumber'], $info['lineNumber']).'</td>
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

    private static function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}
