<?php

class CaptainHookPanel extends BasePanel {

    protected $icon;
    protected $apiBaseUrl;

    public function __construct() {
        if(wire('modules')->isInstalled('ProcessWireAPI')) {
            $ApiModuleId = wire('modules')->getModuleID("ProcessWireAPI");
            $this->apiBaseUrl = wire('pages')->get("process=$ApiModuleId")->url.'methods/';
        }
        else {
            $this->apiBaseUrl = 'https://processwire.com/api/ref/';
        }
    }

    public function getTab() {

        if(\TracyDebugger::isAdditionalBar()) return;
        \Tracy\Debugger::timer('captainHook');

        $this->icon = '
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px"
                 viewBox="0 0 16 16" enable-background="new 0 0 16 16" xml:space="preserve">
            <path fill="#EE1D62" d="M10.9,14.7L10.9,14.7l-0.1-2.1c0-1.2-0.7-2.2-1.8-2.6c0.1-0.1,0.1-0.2,0.1-0.3c0-0.2-0.2-0.5-0.4-0.5V8.4
                c0.9-0.2,1.8-0.7,2.4-1.4c0.7-0.8,1-1.8,1-2.8C12.2,1.9,10.3,0,8,0S3.8,1.9,3.8,4.2c0,0.2,0.1,0.3,0.1,0.3c0.1,0,0.2,0,0.2-0.1
                c1.1-2.6,2.6-3,3.9-3c1.5,0,2.8,1.2,2.8,2.8S9.5,7,8,7C7.8,7,7.6,7,7.4,6.9c-0.1,0-0.1,0-0.2,0.1c-0.1,0.1,0,0.1,0,0.1v2
                C7,9.2,6.8,9.4,6.8,9.7c0,0.1,0,0.2,0.1,0.3c-1.1,0.4-1.8,1.5-1.8,2.6v2.1H5.1c-0.4,0-0.7,0.3-0.7,0.7c0,0.4,0.3,0.7,0.7,0.7h5.9
                c0.4,0,0.7-0.3,0.7-0.7C11.6,15,11.3,14.7,10.9,14.7z"/>
            <path fill="#EE1D62" d="M7.6,7.3c0.1,0,0.3,0,0.4,0c1.7,0,3.1-1.4,3.1-3.1S9.7,1.1,8,1.1c-0.9,0-1.7,0.2-2.3,0.7
                c-0.5,0.4-1,0.9-1.5,1.6C4.6,1.7,6.1,0.4,8,0.4c2.1,0,3.9,1.7,3.9,3.9c0,1.9-1.4,3.6-3.3,3.8c-0.1,0-0.2,0.1-0.2,0.2h0v0.8H7.6
                L7.6,7.3L7.6,7.3z"/>
            <path fill="#EE1D62" d="M7.4,9.5h1.3c0.1,0,0.2,0.1,0.2,0.2c0,0.1-0.1,0.2-0.2,0.2H7.4c-0.1,0-0.2-0.1-0.2-0.2
                C7.2,9.6,7.3,9.5,7.4,9.5z"/>
            <path fill="#EE1D62" d="M5.5,12.6c0-1.1,0.7-2.1,1.8-2.4c0,0,0.1,0,0.1,0h1.3c0,0,0.1,0,0.1,0c1,0.3,1.8,1.3,1.8,2.4v2.1h-5V12.6z"
                />
            <path fill="#EE1D62" d="M10.9,15.6H5.1c-0.2,0-0.3-0.1-0.3-0.3c0-0.2,0.1-0.3,0.3-0.3h5.9c0.2,0,0.3,0.1,0.3,0.3
                C11.2,15.5,11.1,15.6,10.9,15.6z"/>
            </svg>';

        return '
        <span title="Captain Hook">' .
            $this->icon . (\TracyDebugger::getDataValue('showPanelLabels') ? '&nbsp;Captain Hook' : '') . '
        </span>';
    }

    public function getPanel() {

        $out = '
        <h1>' . $this->icon . ' Captain Hook</h1>

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
            function toggleHooks() {

                var panel = document.getElementById("tracy-debug-panel-CaptainHookPanel");
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
            }
        </script>

        <div class="tracy-inner">
            <p><input type="submit" id="toggleAll" onclick="toggleHooks()" value="Toggle All" /></p>
        ';

        $cacheName = 'TracyCaptainHook';
        $cachedHooks = $this->wire('cache')->get($cacheName);
        $configData = $this->wire('modules')->getModuleConfigData("TracyDebugger");

        if(!$cachedHooks || !isset($configData['hooksPwVersion']) || $this->wire('config')->version != $configData['hooksPwVersion']) {
            $configData['hooksPwVersion'] = $this->wire('config')->version;
            $this->wire('modules')->saveModuleConfigData($this->wire('modules')->get("TracyDebugger"), $configData);
            require_once $this->wire('config')->paths->TracyDebugger . 'panels/CaptainHook/GenerateHtml.php';
            $hooks = $this->hooks;
            // sort by filename with Wire Core, Wire Modules, & Site Modules sections
            uasort($hooks, function($a, $b) { return $a['filename']>$b['filename']; });
            $cachedHooks = serialize($hooks);
            $this->wire('cache')->save($cacheName, $cachedHooks);
        }

        $hooks = unserialize($cachedHooks);
        $lastSection = null;
        foreach($hooks as $file => $info) {
            $name = pathinfo($info['filename'], PATHINFO_FILENAME);
            $label = str_replace($this->wire('config')->paths->root, '', $info['filename']);
            $label = \TracyDebugger::forwardSlashPath($label);
            $path = parse_url($label, PHP_URL_PATH);
            $segments = explode('/', $path);
            $currentSection = ucfirst($segments[0]) . ' ' . ucfirst($segments[1]);
            if($currentSection !== $lastSection) $out .= '<h3>'.$currentSection.'</h3>';
            $out .= '
            <a href="#" rel="'.$name.'" class="tracy-toggle tracy-collapsed">'.str_replace($segments[0].'/'.$segments[1].'/', '', $label).'</a>
            <div style="padding-left:10px" id="'.$name.'" class="tracy-collapsed"><p>'.(isset($info['classname']) && (!in_array('site', $segments) || wire('modules')->isInstalled('ProcessWireAPI')) ? '<a href="'.$this->apiBaseUrl.$this->convertNamesToUrls($info['classname']).'/">'.$info['classname'].'</a> ' : $info['classname']).(isset($info['extends']) ? ' extends <a href="'.$this->apiBaseUrl.$this->convertNamesToUrls($info['extends']).'/">'.$info['extends'].'</a>' : '').'</p>'.$this->buildHookTable($info).'</div><br />';
            $lastSection = $currentSection;
        }

        $out .= \TracyDebugger::generatedTimeSize('captainHook', \Tracy\Debugger::timer('captainHook'), strlen($out));
        $out .= '
        </div>';

        return parent::loadResources() . $out;
    }

    private function buildHookTable($info) {
        $out = '
            <table class="captainHookTable">';
        foreach($info['hooks'] as $hook) {
            $out .= '
                <tr>
                    <td>'.$hook['name'].'</td>
                    <td>'.\TracyDebugger::createEditorLink($info['filename'], $hook['lineNumber'], $hook['lineNumber']).'</td>
                    <td class="tracy-force-no-wrap">' . $hook['line'] . '</td>
                </tr>';
        }
        $out .= '
            </table>
        ';
        return $out;
    }

    private function convertNamesToUrls($str) {
        return trim(strtolower(preg_replace('/([A-Z])/', '-$1', $str)), '-');
    }

}
