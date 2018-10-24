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
        <h1>' . $this->icon . ' API Explorer</h1>';

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
                            //manuallyOpened.remove(elId);
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
        foreach($apiVariables as $var => $methods) {
            $out .= '
            <a href="#" rel="'.$var.'" class="tracy-toggle tracy-collapsed">$'.$var.'</a>
            <div style="padding-left:10px" id="'.$var.'" class="tracy-collapsed">' . $this->buildTable($var, $methods) . '</div><br />';
        }


        // Core classes
        $classTypes = array(
            'Primary' => array(
                'Wire',
                'WireData',
                'WireArray',
            ),
            'Pages' => array(
                'Page',
                'NullPage',
                'User',
                'Role',
                'Permission',
            ),
            'Arrays' => array(
                'WireArray',
                'PageArray',
                'PaginatedArray',
            ),
            'Modules' => array(
                'Module',
                'Fieldtype',
                'Inputfield',
                'Process',
                'Textformatter',
            ),

            'Files & Images' => array(
                'Pagefile',
                'Pagefiles',
                'Pageimage',
                'Pageimages',
                'PagefilesManager',
            ),
            'Fields & Templates' => array(
                'Field',
                'Fieldgroup',
                'Template',
            ),
            'Additional' => array(
                'HookEvent',
                'InputfieldWrapper',
                'WireHttp',
                'WireMail',
                'SessionCSRF',
                'ProcessWire',
                'PagesType',
                'Selector',
                'Selectors',
                'WireDatabaseBackup',
                'MarkupPagerNav',
            )
        );

        $coreClasses = array();
        foreach($classTypes as $type => $classes) {
            foreach($classes as $class) {
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

        $out .= '<h3>Core Classes</h3>';
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

        usort($methods, function($a, $b) {
            $aStripped = str_replace(array('___','__'), '', $a->name);
            $bStripped = str_replace(array('___','__'), '', $b->name);
            return $aStripped > $bStripped;
        });

        $properties = array();
        if(isset($this->wire($key)->data) && is_array($this->wire($key)->data)) {

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
                        preg_match_all('#(\$[A-Za-z]+)(?:\s)([A-Za-z`\$->\'’()\s]+)#', $c, $varName);
                        if(isset($varName[1][0])) $propertiesList[$varName[1][0]] = $varName[2][0];
                    }
                }
            }

            //$properties = Page::$baseProperties;
            foreach($propertiesList as $prop => $desc) {
                $properties[str_replace('$', '', $prop)] = $desc;
            }

        }

        uksort($properties, "strnatcasecmp");

        foreach($properties as $name => $desc) {
            $items[$key][$name]['name'] = $name;
            $items[$key][$name]['lineNumber'] = '';
            $items[$key][$name]['filename'] = '';
            $items[$key][$name]['comment'] = "$" . strtolower($key) . '->' . str_replace('___', '', $name);
            if(\TracyDebugger::getDataValue('apiExplorerShowDescription')) {
                $items[$key][$name]['description'] = is_string($desc) ? $desc : Dumper::toHtml($desc, array(Dumper::DEPTH => \TracyDebugger::getDataValue('maxDepth'), Dumper::TRUNCATE => \TracyDebugger::getDataValue('maxLength'), Dumper::COLLAPSE => true));
            }
        }

        foreach($methods as $m) {
            $name = $m->name;
            if(!$r->getMethod($name)->isPublic()) continue;

            $docComment = $r->getMethod($name)->getDocComment();
            $filename = $r->getMethod($name)->getFilename();
            $className = $key;
            $methodName = str_replace(array('___', '__'), '', $name);

            if(strpos($docComment, '#pw-internal') === false && strpos($filename, 'wire') !== false) {
                if($this->apiModuleInstalled || strpos($filename, 'modules') === false) {
                    $items[$key][$name.'()']['name'] = "<a href='".$this->apiBaseUrl.self::convertNamesToUrls($className)."/".self::convertNamesToUrls($methodName)."/'>" . $name . "</a>";
                }
                else {
                    $items[$key][$name.'()']['name'] = $name;
                }
            }
            else {
                $items[$key][$name.'()']['name'] = $name;
            }

            $items[$key][$name.'()']['lineNumber'] = $r->getMethod($name)->getStartLine();
            $items[$key][$name.'()']['filename'] = $filename;
            $i=0;
            foreach($r->getMethod($name)->getParameters() as $param) {
                $items[$key][$name.'()']['params'][$i] = ($param->isOptional() ? '<em>' : '') . '$' . $param->getName() . ($param->isOptional() ? '</em>' : '');
                $i++;
            }

            $methodStr = "$" . strtolower($key) . '->' . str_replace('___', '', $name) . (isset($items[$key][$name.'()']['params']) ? ' (' . implode(', ', $items[$key][$name.'()']['params']) . ')' : '');

            if(\TracyDebugger::getDataValue('apiExplorerToggleDocComment')) {
                $commentStr = "
                <div id='ch-comment'>
                    <label class='".($docComment != '' ? 'comment' : '') . "' for='".$key."_".$name."'>".$methodStr."</label>
                    <input type='checkbox' id='".$key."_".$name."'>
                    <div class='hide'>";
                $items[$key][$name.'()']['comment'] = $commentStr . "\n\n" . nl2br(htmlentities($docComment)) .
                        "</div>
                    </div>";
            }
            else {
                $items[$key][$name.'()']['comment'] = $methodStr;
            }

            if(\TracyDebugger::getDataValue('apiExplorerShowDescription')) {
                // get the comment
                preg_match('#^/\*\*(.*)\*/#s', $docComment, $comment);
                if(isset($comment[0])) $comment = trim($comment[0]);
                // get all the lines and strip the * from the first character
                if(is_string($comment)) {
                    preg_match_all('#^\s*\*(.*)#m', $comment, $commentLines);
                    $items[$key][$name.'()']['description'] = isset($commentLines[1][0]) && is_string($commentLines[1][0]) ? nl2br(htmlentities(trim($commentLines[1][0]))) : '';
                }
                else {
                    $items[$key][$name.'()']['description'] = '';
                }
            }

        }

        return $items;

    }


    private function buildTable($var, $items) {
        $out = '
            <table class="apiExplorerTable">';
        $i=0;
        $methodsSection = false;
        foreach($items as $item => $info) {
            if(strpos($item, '()') !== false && !$methodsSection) {
                $methodsSection = true;
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.strtolower($var).' methods</th>';
            }
            elseif($i == 0) {
                $out .= '<th colspan="'.(\TracyDebugger::getDataValue('apiExplorerShowDescription') ? '4' : '3').'">$'.strtolower($var).' properties</th>';
            }
            $out .= '
                <tr>
                    <td>'.$info['name'].'</td>
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
